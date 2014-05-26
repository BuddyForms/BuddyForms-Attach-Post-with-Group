<?php
/*
 Plugin Name: BuddyForms Attach Posts to Groups Extension
 Plugin URI: http://themekraft.com
 Description: Create engaged communities with every post.
 Version: 1.0.1
 Author: Sven Lehnert
 Author URI: http://themekraft.com
 Licence: GPLv3
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

	require (dirname(__FILE__) . '/buddyforms-groups.php');
	$buddyforms_Group_Extension = new BuddyForms_Group_Extension();
}

add_action('bp_loaded', 'buddyforms_Group_Extension_init', 0);