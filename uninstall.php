<?php // uninstall remove options

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

// Delete global options
delete_option('shfc_global_code');

// Delete post meta data for all posts
global $wpdb;
$wpdb->delete($wpdb->postmeta, array('meta_key' => 'shfc_single_code'));
