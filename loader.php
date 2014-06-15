<?php
/*
 Plugin Name: BuddyForms Attach Posts to Groups Extension
 Plugin URI: http://themekraft.com
 Description: Create engaged communities with every post.
 Version: 1.0.1
 Author: Sven Lehnert
 Author URI: http://themekraft.com
 Licence: GPLv3

 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
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