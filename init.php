<?php
/**
 * Plugin name: SEO Header Footer Code
 * Author: Deep Goyal
 * Plugin URI: https://wpexpertdeep.com
 * Author URI: https://wpexpertdeep.com
 * Description: This is a plugin to add custom code in header, footer, body for posts, pages.
 * Tested up to: 6.1
 * version: 1.0
 * License: GPL 3.0, @see http://www.gnu.org/licenses/gpl-3.0.html
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'SHFC_ADMIN_VERSION', '1.0' );
define('SHFC_ADMIN_DIR', plugin_dir_path( __FILE__ ));
define('SHFC_ADMIN_URL', plugin_dir_url( __FILE__ ));

add_action('admin_menu', 'shfc_admin_menu_register');
function shfc_admin_menu_register(){
	add_options_page( 
		'SEO Header Footer Code', 
		'SEO Header Footer Code', 
		'manage_options', 
		'shfc-manager', 
		'shfc_manager_func', 
	);
}


function default_options() {
	$option_values = [
		'global_header_code' => '',
		'global_body_code' => '',
		'global_footer_code' => ''
	];
	return apply_filters('shfc_default_options', $option_values);
}

/**
 * Load Setting Page
 */

function shfc_manager_func(){
	$shfc_option_values = get_option('shfc_global_code', default_options());
	include_once SHFC_ADMIN_DIR . 'partials/shfc-settings.php';
}


/**
 * Register Setting for global header footer code
 */

add_action('admin_init', 'shfc_admin_settings');
function shfc_admin_settings(){
	register_setting('shfc_plugin_options', 'shfc_global_code', array('validate_global_code'));
}


function validate_global_code($code){
	if (isset($code['global_header_code'])){
		$code['global_header_code'] = stripslashes($code['global_header_code']);
	} 

	if (isset($code['global_body_code'])){
		$code['global_body_code'] = stripslashes($code['global_body_code']);
	} 

	if (isset($code['global_footer_code'])){
		$code['global_footer_code'] = stripslashes($code['global_footer_code']);
	} 
			
	return $code;
}



// add custom meta boxes to add custom header, body and footer code 
add_action( 'add_meta_boxes', 'shfc_post_meta_fields' );
function shfc_post_meta_fields() {

    add_meta_box(
        'shfc-custom-code-post-meta-field',
        'SEO Header Footer Code:',
        'shfc_post_meta_field_callback'
    );
}

function shfc_post_meta_field_callback($post){
	// Add a nonce field so we can check for it later.
    wp_nonce_field( 'shfc_custom_code_nonce', 'shfc_custom_code_nonce' );

    $shfc_single_meta = get_post_meta( $post->ID, 'shfc_single_code', true );
    $shfc_header_code = array_key_exists("header_code", $shfc_single_meta) ? $shfc_single_meta['header_code'] : ''; 

    $shfc_body_code = array_key_exists("body_code", $shfc_single_meta) ? $shfc_single_meta['body_code'] : ''; 

    $shfc_footer_code = array_key_exists("footer_code", $shfc_single_meta) ? $shfc_single_meta['footer_code'] : ''; 

    // print_r($shfc_single_meta);

    echo '<lable>Insert Header Code here:</lable>
    <textarea style="width:100%; height: 80px; margin-bottom: 30px" id="shfc_single_header" name="shfc_single_header">' . esc_attr( $shfc_header_code ) . '</textarea><br>';

    echo '<lable>Insert Body Code here:</lable><textarea style="width:100%; height: 80px; margin-bottom: 30px" id="shfc_single_body" name="shfc_single_body">' . esc_attr( $shfc_body_code ) . '</textarea><br>';

    echo '<lable>Insert Footer Code here:</lable><textarea style="width:100%; height: 80px;" id="shfc_single_footer" name="shfc_single_footer">' . esc_attr( $shfc_footer_code ) . '</textarea>';
}



add_action( 'save_post', 'save_shfc_post_meta_box_data' );

function save_shfc_post_meta_box_data($post_id){
	 // Check if our nonce is set.
    if ( ! isset( $_POST['shfc_custom_code_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['shfc_custom_code_nonce'], 'shfc_custom_code_nonce' ) ) {
        return;
    }

    echo "hello";


    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'page') {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // entered post meta values
    $shfc_header_code = isset($_POST['shfc_single_header']) ? $_POST['shfc_single_header'] : '';
    $shfc_body_code = isset($_POST['shfc_single_body']) ? $_POST['shfc_single_body'] : '';
    $shfc_footer_code = isset($_POST['shfc_single_footer']) ? $_POST['shfc_single_footer'] : '';

    $shfc_custom_meta_values = [
    	'header_code' =>  $shfc_header_code,
    	'body_code' =>  $shfc_body_code,
    	'footer_code' =>  $shfc_footer_code
    ];


    // Update the meta field in the database.
    update_post_meta( $post_id, 'shfc_single_code', $shfc_custom_meta_values );
}




/**
 * Insert custom code in header
 */

add_action('wp_head', 'shfc_inject_code_in_header');
function shfc_inject_code_in_header(){
	// insert global header code in <head> 
	$options = get_option('shfc_global_code');
	if(isset($options['global_header_code'])){
		echo $options['global_header_code'];
	}

	// get post, page header code 
	$post_id = get_the_ID();
	$shfc_value = get_post_meta( $post_id, 'shfc_single_code', true );

	if(array_key_exists('header_code', $shfc_value)){
		echo $shfc_value['header_code'];
	}

}


/**
 * Insert custom code in footer
 */

add_action('wp_footer', 'shfc_inject_code_in_footer');
function shfc_inject_code_in_footer(){
	// insert global body code in <body> 
	$options = get_option('shfc_global_code');
	if(isset($options['global_footer_code'])){
		echo $options['global_footer_code'];
	} 


	// get post, page footer code 
	$post_id = get_the_ID();
	$shfc_value = get_post_meta( $post_id, 'shfc_single_code', true );

	if(array_key_exists('footer_code', $shfc_value)){
		echo $shfc_value['footer_code'];
	}
}


/**
 * Insert custom code in body
 */

add_action('wp_body_open', 'shfc_inject_code_after_body');
function shfc_inject_code_after_body(){
	// insert global footer code in footer 
	$options = get_option('shfc_global_code');
	if(isset($options['global_body_code'])){
		echo $options['global_body_code'];
	} 

	// get post, page footer code 
	$post_id = get_the_ID();
	$shfc_value = get_post_meta( $post_id, 'shfc_single_code', true );

	if(array_key_exists('body_code', $shfc_value)){
		echo $shfc_value['body_code'];
	}
}