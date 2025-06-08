<?php

/**
 * Plugin name: SEO Header Footer Master
 * Description: This is a lightweight plugin to add custom code in header, footer, body for posts, pages or global.
 * Tags: header, footer, analytics, seo, custom-code
 * Author: Deep Goyal
 * Plugin URI: https://wordpressbrain.com
 * Author URI: https://wpexpertdeep.com
 * Tested up to: 6.8
 * Version: 1.2.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: seo-header-footer-master
 * 
 */

if (! defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('SHFC_ADMIN_VERSION', '1.0');
define('SHFC_ADMIN_DIR', plugin_dir_path(__FILE__));
define('SHFC_ADMIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class SEO_Header_Footer_Code
{
	/**
	 * Initialize the plugin
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'register_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta_box_data'));
		add_action('wp_head', array($this, 'inject_header_code'));
		add_action('wp_footer', array($this, 'inject_footer_code'));
		add_action('wp_body_open', array($this, 'inject_body_code'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
	}

	/**
	 * Register admin menu
	 */
	public function register_admin_menu()
	{
		add_options_page(
			'SEO Header Footer Master',
			'SEO Header Footer Master',
			'manage_options',
			'shfc-manager',
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Get default options
	 */
	private function get_default_options()
	{
		$option_values = array(
			'global_header_code' => '',
			'global_body_code' => '',
			'global_footer_code' => '',
			'allowed_post_types' => array('post', 'page')
		);
		return apply_filters('shfc_default_options', $option_values);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page()
	{
		$shfc_option_values = get_option('shfc_global_code', $this->get_default_options());
		include_once SHFC_ADMIN_DIR . 'partials/shfc-settings.php';
	}

	/**
	 * Register settings
	 */
	public function register_settings()
	{
		register_setting('shfc_plugin_options', 'shfc_global_code', array($this, 'validate_global_code'));
	}

	/**
	 * Validate global code
	 */
	public function validate_global_code($code)
	{
		if (!is_array($code)) {
			return $this->get_default_options();
		}

		$sanitized_code = array();

		if (isset($code['global_header_code'])) {
			$sanitized_code['global_header_code'] = wp_unslash($code['global_header_code']);
		}

		if (isset($code['global_body_code'])) {
			// Allow script tags in body code
			$sanitized_code['global_body_code'] = wp_unslash($code['global_body_code']);
		}

		if (isset($code['global_footer_code'])) {
			// Allow script tags in footer code
			$sanitized_code['global_footer_code'] = wp_unslash($code['global_footer_code']);
		}

		if (isset($code['allowed_post_types'])) {
			$sanitized_code['allowed_post_types'] = array_map('sanitize_text_field', $code['allowed_post_types']);
			// Validate that all post types exist
			$sanitized_code['allowed_post_types'] = array_filter($sanitized_code['allowed_post_types'], 'post_type_exists');
		}

		return $sanitized_code;
	}

	/**
	 * Add meta boxes
	 */
	public function add_meta_boxes()
	{
		$options = get_option('shfc_global_code', $this->get_default_options());
		$allowed_post_types = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : array('post', 'page');

		foreach ($allowed_post_types as $post_type) {
			add_meta_box(
				'shfc-custom-code-post-meta-field',
				'SEO Header Footer Master:',
				array($this, 'render_meta_box'),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render meta box
	 */
	public function render_meta_box($post)
	{
		wp_nonce_field('shfc_custom_code_nonce', 'shfc_custom_code_nonce');

		$shfc_single_meta = get_post_meta($post->ID, 'shfc_single_code', true);
		$shfc_header_code = '';
		$shfc_body_code = '';
		$shfc_footer_code = '';

		if (!empty($shfc_single_meta)) {
			$shfc_header_code = isset($shfc_single_meta['header_code']) ? wp_unslash($shfc_single_meta['header_code']) : '';
			$shfc_body_code = isset($shfc_single_meta['body_code']) ? wp_kses_post($shfc_single_meta['body_code']) : '';
			$shfc_footer_code = isset($shfc_single_meta['footer_code']) ? wp_kses_post($shfc_single_meta['footer_code']) : '';
		}

?>
		<label for="shfc_single_header">Insert Header Code here:</label>
		<textarea style="width:100%; height: 80px; margin-bottom: 30px" id="shfc_single_header" name="shfc_single_header"><?php echo esc_textarea($shfc_header_code); ?></textarea>

		<label for="shfc_single_body">Insert Body Code here:</label>
		<textarea style="width:100%; height: 80px; margin-bottom: 30px" id="shfc_single_body" name="shfc_single_body"><?php echo esc_textarea($shfc_body_code); ?></textarea>

		<label for="shfc_single_footer">Insert Footer Code here:</label>
		<textarea style="width:100%; height: 80px;" id="shfc_single_footer" name="shfc_single_footer"><?php echo esc_textarea($shfc_footer_code); ?></textarea>
<?php
	}

	/**
	 * Save meta box data
	 */
	public function save_meta_box_data($post_id)
	{
		// Check if our nonce is set and verify that the nonce is valid
		if (!isset($_POST['shfc_custom_code_nonce']) || !wp_verify_nonce($_POST['shfc_custom_code_nonce'], 'shfc_custom_code_nonce')) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions
		$post_type = get_post_type($post_id);
		if ($post_type === 'page') {
			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		// Sanitize and validate the input
		$shfc_header_code = isset($_POST['shfc_single_header']) ? wp_unslash($_POST['shfc_single_header']) : '';
		$shfc_body_code = isset($_POST['shfc_single_body']) ? wp_unslash($_POST['shfc_single_body']) : '';
		$shfc_footer_code = isset($_POST['shfc_single_footer']) ? wp_unslash($_POST['shfc_single_footer']) : '';

		// Only update if there's actual content
		if (!empty($shfc_header_code) || !empty($shfc_body_code) || !empty($shfc_footer_code)) {
			$shfc_custom_meta_values = array(
				'header_code' => $shfc_header_code,
				'body_code' => $shfc_body_code,
				'footer_code' => $shfc_footer_code
			);

			update_post_meta($post_id, 'shfc_single_code', $shfc_custom_meta_values);
		} else {
			// If all fields are empty, delete the meta
			delete_post_meta($post_id, 'shfc_single_code');
		}
	}

	/**
	 * Inject header code
	 */
	public function inject_header_code()
	{
		$options = get_option('shfc_global_code', $this->get_default_options());
		$allowed_post_types = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : array('post', 'page');

		// Always show global header code regardless of post type
		if (isset($options['global_header_code']) && !empty($options['global_header_code'])) {
			echo wp_unslash($options['global_header_code']);
		}

		// Only show post-specific code if we're on a single post/page and it's an allowed post type
		if (is_singular()) {
			$post_id = get_the_ID();
			$post_type = get_post_type($post_id);

			if (in_array($post_type, $allowed_post_types)) {
				$shfc_value = get_post_meta($post_id, 'shfc_single_code', true);
				if (!empty($shfc_value) && isset($shfc_value['header_code'])) {
					echo wp_unslash($shfc_value['header_code']);
				}
			}
		}
	}

	/**
	 * Inject footer code
	 */
	public function inject_footer_code()
	{
		$options = get_option('shfc_global_code', $this->get_default_options());
		$allowed_post_types = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : array('post', 'page');

		// Always show global footer code regardless of post type
		if (isset($options['global_footer_code']) && !empty($options['global_footer_code'])) {
			echo wp_unslash($options['global_footer_code']);
		}

		// Only show post-specific code if we're on a single post/page and it's an allowed post type
		if (is_singular()) {
			$post_id = get_the_ID();
			$post_type = get_post_type($post_id);

			if (in_array($post_type, $allowed_post_types)) {
				$shfc_value = get_post_meta($post_id, 'shfc_single_code', true);
				if (!empty($shfc_value) && isset($shfc_value['footer_code'])) {
					echo wp_unslash($shfc_value['footer_code']);
				}
			}
		}
	}

	/**
	 * Inject body code
	 */
	public function inject_body_code()
	{
		$options = get_option('shfc_global_code', $this->get_default_options());
		$allowed_post_types = isset($options['allowed_post_types']) ? $options['allowed_post_types'] : array('post', 'page');

		// Always show global body code regardless of post type
		if (isset($options['global_body_code']) && !empty($options['global_body_code'])) {
			echo wp_unslash($options['global_body_code']);
		}

		// Only show post-specific code if we're on a single post/page and it's an allowed post type
		if (is_singular()) {
			$post_id = get_the_ID();
			$post_type = get_post_type($post_id);

			if (in_array($post_type, $allowed_post_types)) {
				$shfc_value = get_post_meta($post_id, 'shfc_single_code', true);
				if (!empty($shfc_value) && isset($shfc_value['body_code'])) {
					echo wp_unslash($shfc_value['body_code']);
				}
			}
		}
	}

	/**
	 * Add settings link to plugin listing
	 */
	public function add_plugin_action_links($links)
	{
		$settings_link = '<a href="' . admin_url('options-general.php?page=shfc-manager') . '">' . __('Settings', 'seo-header-footer-master') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
}

// Initialize the plugin
new SEO_Header_Footer_Code();
