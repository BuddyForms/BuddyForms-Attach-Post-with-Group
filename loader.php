<?php
/*
 Plugin Name: BuddyForms Attach Post with Group
 Plugin URI: http://buddyforms.com/downloads/attach-post-with-group/
 Description: Create engaged communities with every post.
 Requires at least: 4.0
 Tested up to: 4.3.1
 Version: 1.1.4
 Author: Sven Lehnert
 Author URI: https://profiles.wordpress.org/svenl77
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

define('BuddyForms_Attach_Post_with_Group', '1.1.4');

/**
 * Loads BuddyForms Attach Posts to Groups Extension files only if BuddyPress is present
 *
 * @package BuddyForms Attach Posts to Groups Extension
 * @since 0.1-beta
 */
//add_action('bp_loaded', 'bf_aptg_bp_loaded');
//function bf_aptg_bp_loaded() {
    global $buddyforms_group_extension;

    require_once (dirname(__FILE__) . '/buddyforms-groups.php');
    $buddyforms_group_extension = new BuddyForms_Group_Extension();


//}

function bf_aptg_register_widgets() {

    require_once (dirname(__FILE__) . '/includes/widgets/' . 'widget-attached-group.php');
    require_once (dirname(__FILE__) . '/includes/widgets/' . 'widget-group-list-moderators.php');
    require_once (dirname(__FILE__) . '/includes/widgets/' . 'widget-all-posts-of-displayed-group.php');

    register_widget( 'BuddyForms_Attached_Group_Widget' );
    register_widget( 'BuddyForms_List_Moderators_Widget' );
    register_widget( 'BuddyForms_All_Posts_of_this_Group_Widget' );

}
add_action( 'widgets_init', 'bf_aptg_register_widgets' );