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

// setting page
function shfc_manager_func(){
	$shfc_option_values = get_option('shfc_global_code', default_options());

	include_once SHFC_ADMIN_DIR . 'partials/shfc-settings.php';
}



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


// add global code to header 
add_action('wp_head', 'shfc_inject_code_in_header');
function shfc_inject_code_in_header(){
	$options = get_option('shfc_global_code');
	if(isset($options['global_header_code'])){
		echo $options['global_header_code'];
	} 
}


// add global code to footer 
add_action('wp_footer', 'shfc_inject_code_in_footer');
function shfc_inject_code_in_footer(){
	$options = get_option('shfc_global_code');
	if(isset($options['global_footer_code'])){
		echo $options['global_footer_code'];
	} 
}

// add global code in body tag 
add_action('wp_body_open', 'shfc_inject_code_after_body');
function shfc_inject_code_after_body(){
	$options = get_option('shfc_global_code');
	if(isset($options['global_body_code'])){
		echo $options['global_body_code'];
	} 
}