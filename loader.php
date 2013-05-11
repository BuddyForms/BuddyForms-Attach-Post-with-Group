<?php
/*
 Plugin Name: CPT4BP-Group-Extension
 Plugin URI: http://themekraft.com
 Description:   
 Version: 0.1 beta
 Author: Sven Lehnert
 Author URI: http://themekraft.com
 Licence: GPLv3
 Network: true
 */

define('CPT4BP-Group-Extension', '0.1');

/**
 * Loads CPT4BP-Group-Extension files only if BuddyPress is present
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */
function CPT4BP_Group_Extension_init() {
	global $wpdb;

	if (is_multisite() && BP_ROOT_BLOG != $wpdb->blogid)
		return;

	require (dirname(__FILE__) . '/CPT4BP-Group-Extension.php');
	$CPT4BP_Group_Extension = new CPT4BP_Group_Extension();
}

add_action('bp_loaded', 'CPT4BP_Group_Extension_init', 0);
