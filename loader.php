<?php
/*
 Plugin Name: BuddyForms-Group-Extension
 Plugin URI: http://themekraft.com
 Description:   
 Version: 0.1 beta
 Author: Sven Lehnert
 Author URI: http://themekraft.com
 Licence: GPLv3
 Network: true
 */

define('buddyforms-Group-Extension', '0.1');

/**
 * Loads buddyforms-Group-Extension files only if BuddyPress is present
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */
function buddyforms_Group_Extension_init() {
	global $wpdb;

	if (is_multisite() && BP_ROOT_BLOG != $wpdb->blogid)
		return;

	require (dirname(__FILE__) . '/buddyforms-Group-Extension.php');
	$buddyforms_Group_Extension = new buddyforms_Group_Extension();
}

add_action('bp_loaded', 'buddyforms_Group_Extension_init', 0);
