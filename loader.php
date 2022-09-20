<?php
/*
 * Plugin Name: BuddyForms Attach Post with Group
 * Plugin URI: http://buddyforms.com/downloads/attach-post-with-group/
 * Description: Create engaged communities with every post.
 * Requires at least: 3.9
 * Tested up to: 6.0.2
 * Version: 1.2.12
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/buddyforms/
 * Licence: GPLv3
 * Text Domain: buddyforms
 * Svn: buddyforms-attach-posts-to-groups-extension
 *
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

class BuddyForms_Group_Extension {
	public $post_type_name;
	public $associated_item_tax_name;

	/**
	 * Initiate the class
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function __construct() {
		$this->init_hook();
		$this->load_constants();

		// Load all needed files
		add_action( 'init', array( $this, 'includes' ), 1 );

		// Load the plugin translation files
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 10, 1 );

		// Register the Group Type Taxonomies for the relationships
		add_action( 'init', array( $this, 'register_taxonomy' ), 10, 2 );

		// Post to Group redirect
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 999, 2 );

		// Register all available widgets
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		// Create the Groups Component.
		add_action( 'bp_init', array( $this, 'setup_group_extension' ), 10, 1 );

		// Rewrite the post type link
		add_filter( 'post_type_link', array( $this, 'post_type_link_remove_slug' ), 1, 3 );

	}

	/**
	 * Defines buddyforms_init action
	 *
	 * This action fires on WP's init action and provides a way for the rest of WP,
	 * as well as other dependent plugins, to hook into the loading process in an
	 * orderly fashion.
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function init_hook() {
		do_action( 'buddyforms_ge_init' );
	}

	/**
	 * Defines constants needed throughout the plugin.
	 *
	 * These constants can be overridden in bp-custom.php or wp-config.php.
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function load_constants() {

		define( 'BuddyForms_Attach_Post_with_Group', '1.2.12' );

		if ( ! defined( 'BUDDYFORMS_GE_INSTALL_PATH' ) ) {
			define( 'BUDDYFORMS_GE_INSTALL_PATH', dirname( __FILE__ ) . '/' );
		}

		if ( ! defined( 'BUDDYFORMS_GE_INCLUDES_PATH' ) ) {
			define( 'BUDDYFORMS_GE_INCLUDES_PATH', BUDDYFORMS_GE_INSTALL_PATH . 'includes/' );
		}

		if ( ! defined( 'BUDDYFORMS_GE_TEMPLATE_PATH' ) ) {
			define( 'BUDDYFORMS_GE_TEMPLATE_PATH', BUDDYFORMS_GE_INCLUDES_PATH . 'templates/' );
		}

	}

	/**
	 * Includes files needed by buddyforms
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function includes() {

		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'group-control.php' );
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'functions.php' );
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'form-elements.php' );
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'shortcodes.php' );

	}

	/**
	 * Loads the textdomain for the plugin
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'buddyforms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Load the group extension file
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function setup_group_extension() {

		if ( defined( 'BP_VERSION' ) && bp_is_active( 'groups' ) ) {
			require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'group-extension.php' );
		}

	}

	/**
	 * Registers BuddyPress buddyforms taxonomies for apwg_taxonomys
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function register_taxonomy() {
		global $buddyforms;

//		if ( defined( 'DOING_AJAX' ) ) {
//			return;
//		}

		if ( ! isset( $buddyforms ) ) {
			return;
		}

		if ( ! is_array( $buddyforms ) ) {
			return;
		}

		foreach ( $buddyforms as $form_slug => $buddyform ) :

			if ( ! isset( $buddyform['post_type'] ) || $buddyform['post_type'] == 'bf_submissions' ) {
				continue;
			}

			if ( isset( $buddyform['form_fields'] ) ) {
				foreach ( $buddyform['form_fields'] as $field_key => $form_field ) {

					if ( isset( $form_field['type'] ) && $form_field['type'] == 'apwg_taxonomy' ) {

						$attached_form_slug = $form_field['apwg_taxonomy'];
						$attached_post_type = $buddyforms[ $attached_form_slug ]['post_type'];

						$labels_group_groups = array(
							'name' => $form_field['name'],
						);

						register_taxonomy( 'bf_apwg_' . $form_field['slug'], $buddyform['post_type'], array(
								'hierarchical'      => true,
								'labels'            => $labels_group_groups,
								'show_ui'           => true,
								'query_var'         => true,
								'rewrite'           => array( 'slug' => 'bf_apwg_' . $form_field['slug'] ),
								'show_in_nav_menus' => false,
							)
						);

						// register_taxonomy_for_object_type( 'bf_apwg_' . $form_field['slug'], $buddyform['post_type'] );

						$terms = get_terms(
							'bf_apwg_' . $form_field['slug'],
							array(
								'fields'     => 'all',
								'hide_empty' => false
							)
						);

						if ( $terms ) {
							foreach ( $terms as $term_key => $term ) {

								$cat_posts = array(
									'tax_query'      => array(
										array(
											'taxonomy' => $term->taxonomy,
											'field'    => 'id',
											'terms'    => $term->term_id,
										)
									),
									'post_type'      => $attached_post_type, // my custom post type
									'posts_per_page' => 1, // show all posts
									'post_status'    => 'publish',
									'meta_key'       => '_bf_form_slug',
									'meta_value'     => $attached_form_slug
								);

								$cat_posts = get_posts( $cat_posts );

								if ( count( $cat_posts ) < 1 ) {
									wp_delete_term( $term->term_id, 'bf_apwg_' . $form_field['slug'] );
								}

							}
						} else {

							bf_apwg_generate_attached_tax( $form_field['slug'], $attached_post_type, $attached_form_slug );

						}
					}
				}
			}
		endforeach;
	}


	/**
	 * Change the slug to groups slug to keep it consistent
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function post_type_link_remove_slug( $permalink, $post, $leavename ) {
		global $buddyforms, $bp;

		if ( ! isset( $buddyforms ) ) {
			return $permalink;
		}

		if ( ! defined( 'BP_VERSION' ) ) {
			return $permalink;
		}

		if ( ! bp_is_active( 'groups' ) ) {
			return $permalink;
		}

		$post_group_id = get_post_meta( $post->ID, '_post_group_id', true );
		$bf_form_slug  = get_post_meta( $post->ID, '_bf_form_slug', true );

		if ( ! isset( $buddyforms[ $bf_form_slug ]['groups']['redirect'] ) ) {
			return $permalink;
		}

		$group_post_id = groups_get_groupmeta( $post_group_id, 'group_post_id' );

		if ( $post->ID != $group_post_id ) {
			return $permalink;
		}

		if ( isset( $buddyforms[ $bf_form_slug ]['groups']['attache'] ) ) {
			$permalink = get_bloginfo( 'url' ) . '/' . $bp->groups->root_slug . '/' . basename( $permalink );
		}

		return $permalink;
	}

	/**
	 * Redirect a post to its group
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function template_redirect() {

		global $wp_query, $post, $buddyforms;

		if ( ! isset( $buddyforms ) ) {
			return;
		}

		if ( ! defined( 'BP_VERSION' ) ) {
			return;
		}

		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}


		if ( bp_is_group_single() ) {
			return;
		}

		if ( ! isset( $post ) ) {
			return;
		}

		$bf_form_slug = get_post_meta( get_the_ID(), '_bf_form_slug', true );

		if ( ! isset( $buddyforms[ $bf_form_slug ]['groups']['attache'] ) ) {
			return;
		}

		if ( ! isset( $buddyforms[ $bf_form_slug ]['groups']['redirect'] ) ) {
			return;
		}

		$post_group_id = get_post_meta( get_the_ID(), '_post_group_id', true );
		$group_post_id = groups_get_groupmeta( $post_group_id, 'group_post_id' );

		if ( get_the_ID() != $group_post_id ) {
			return;
		}

		if ( is_singular() ) {
			$link = get_bloginfo( 'url' ) . '/' . BP_GROUPS_SLUG . '/' . get_post_meta( $wp_query->post->ID, '_link_to_group', true );
			wp_redirect( $link, '301' );
			exit;
		}
	}

	function widgets_init() {

		// WIDGETS
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'widgets/widget-attached-group.php' );
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'widgets/widget-group-list-moderators.php' );
		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'widgets/widget-all-posts-of-displayed-group.php' );

		register_widget( 'BuddyForms_APWG_Taxonomy_Term_Post_Widget' );
		register_widget( 'BuddyForms_List_Moderators_Widget' );
		register_widget( 'BuddyForms_All_Posts_of_this_Group_Widget' );
	}
}

new BuddyForms_Group_Extension();

//
// Check the plugin dependencies
//
add_action( 'init', function () {

	// Only Check for requirements in the admin
	if ( ! is_admin() ) {
		return;
	}

	// Require TGM
	require( dirname( __FILE__ ) . '/includes/resources/tgm/class-tgm-plugin-activation.php' );

	// Hook required plugins function to the tgmpa_register action
	add_action( 'tgmpa_register', function () {

		// Create the required plugins array
		$plugins['buddypress'] = array(
			'name'     => 'BuddyPress',
			'slug'     => 'buddypress',
			'required' => true,
		);


		if ( ! defined( 'BUDDYFORMS_PRO_VERSION' ) ) {
			$plugins['buddyforms'] = array(
				'name'     => 'BuddyForms',
				'slug'     => 'buddyforms',
				'required' => true,
			);
		}

		$config = array(
			'id'           => 'buddyforms-tgmpa',  // Unique ID for hashing notices for multiple instances of TGMPA.
			'parent_slug'  => 'plugins.php',       // Parent menu slug.
			'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,                // Show admin notices or not.
			'dismissable'  => false,               // If false, a user cannot dismiss the nag message.
			'is_automatic' => true,                // Automatically activate plugins after installation or not.
		);

		// Call the tgmpa function to register the required plugins
		tgmpa( $plugins, $config );

	} );
}, 1, 1 );

// Create a helper function for easy SDK access.
function baptge_fs() {
	global $baptge_fs;

	if ( ! isset( $baptge_fs ) ) {
		// Include Freemius SDK.
		if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php' ) ) {
			// Try to load SDK from parent plugin folder.
			require_once dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php';
		} else if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php' ) ) {
			// Try to load SDK from premium parent plugin folder.
			require_once dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php';
		}

		$baptge_fs = fs_dynamic_init( array(
			'id'             => '407',
			'slug'           => 'buddyforms-attach-posts-to-groups-extension',
			'type'           => 'plugin',
			'public_key'     => 'pk_c133f19751d39a5cf3cf3ef9a5129',
			'is_premium'     => false,
			'has_paid_plans' => false,
			'is_org_compliant' => false,
			'parent'         => array(
				'id'         => '391',
				'slug'       => 'buddyforms',
				'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
				'name'       => 'BuddyForms',
			),
			'menu'           => array(
				'slug'    => 'edit.php?post_type=buddyforms',
				'support' => false,
			),
		) );
	}

	return $baptge_fs;
}

function baptge_fs_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function baptge_fs_is_parent_active() {
	$active_plugins_basenames = get_option( 'active_plugins' );

	foreach ( $active_plugins_basenames as $plugin_basename ) {
		if ( 0 === strpos( $plugin_basename, 'buddyforms/' ) ||
		     0 === strpos( $plugin_basename, 'buddyforms-premium/' )
		) {
			return true;
		}
	}

	return false;
}

function baptge_fs_init() {
	if ( baptge_fs_is_parent_active_and_loaded() ) {
		// Init Freemius.
		baptge_fs();

		// Parent is active, add your init code here.
	} else {
		// Parent is inactive, add your error handling here.
	}
}

if ( baptge_fs_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	baptge_fs_init();
} else if ( baptge_fs_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'baptge_fs_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	baptge_fs_init();
}