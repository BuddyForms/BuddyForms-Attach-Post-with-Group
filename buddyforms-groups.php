<?php

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

		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 10, 1 );
		add_action( 'register_taxonomy', array( $this, 'register_taxonomy' ), 10, 2 );
		add_action( 'bp_init', array( $this, 'setup_group_extension' ), 10, 1 );
		add_action( 'template_redirect', array( $this, 'theme_redirect' ), 999, 2 );

		add_filter( 'post_type_link', array( $this, 'remove_slug' ), 1, 3 );

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

		/*if(!function_exists('buddyforms_wp_list_post_revisions'))
			require_once (BUDDYFORMS_INCLUDES_PATH		. 'revisions.php');*/

		require_once( BUDDYFORMS_GE_INCLUDES_PATH . 'group-extension.php' );

	}

	/**
	 * Registers BuddyPress buddyforms taxonomies for attachgrouptypes
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function register_taxonomy() {
		global $buddyforms;

		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! isset( $buddyforms ) ) {
			return;
		}

		if ( ! is_array( $buddyforms ) ) {
			return;
		}

		foreach ( $buddyforms as $form_slug => $buddyform ) :

			if ( ! isset( $buddyform['post_type'] ) || $buddyform['post_type'] == 'none' ) {
				continue;
			}

			if ( isset( $buddyform['form_fields'] ) ) {
				foreach ( $buddyform['form_fields'] as $field_key => $form_field ) {

					if ( isset( $form_field['type'] ) && $form_field['type'] == 'attachgrouptype' ) {

//						print_r($form_field);

						$attachgrouptype = $form_field['attachgrouptype'];


						$labels_group_groups = array(
							'name'          => sprintf( __( '%s Categories' ), $form_field['name'] ),
							'singular_name' => sprintf( __( '%s Category' ), $form_field['name'] )
						);

						$attach_group_post_type = $buddyforms[ $attachgrouptype ]['post_type'];

						register_taxonomy( $form_slug . '_attached_' . $attach_group_post_type, $buddyform['post_type'], array(
								'hierarchical'      => true,
								'labels'            => $labels_group_groups,
								'show_ui'           => true,
								'query_var'         => true,
								'rewrite'           => array( 'slug' => $form_slug . '_attached_' . $form_field['attachgrouptype'] ),
								'show_in_nav_menus' => false,
							)
						);





						$terms = get_terms(
							$form_slug . '_attached_' . $attach_group_post_type,
							array(
								'fields' => 'all',
								'hide_empty' => false
							)
						);
						//print_r($terms);
						if ( $terms ) {
							foreach ( $terms as $term_key => $term ) {

								$args2 = array(
									'tax_query' => array(
										array(
											'taxonomy' => $term->taxonomy,
											'field' => 'id',
											'terms' => $term->term_id,
										)),
									'post_type'      => $attach_group_post_type, // my custom post type
									'posts_per_page' => 1, // show all posts
									'post_status'    => 'publish',
									'meta_key'       => '_post_group_id',
//									'meta_value'     => $form_slug
								);

//								print_r(get_posts($args2));

								echo 'count(get_posts($args2)) ' . count(get_posts($args2)) . '<br>';
								if(count(get_posts($args2)) ){
									echo 'delete' . $term->term_id;
									wp_delete_term( $term->term_id, $form_slug . '_attached_' . $attach_group_post_type );
								}

							}
						}




						$args = array(
							'post_type'      => $attach_group_post_type,
							'posts_per_page' => - 1,
							'post_status'    => 'publish',
							'meta_query' => array(
								array(
									'key'     => '_post_group_id',
								),
								array(
									'key' => '_bf_form_slug',
									'value'   => $attachgrouptype,
								),
							),
						);

						$attached_posts = new WP_Query( $args );

						while ( $attached_posts->have_posts() ) : $attached_posts->the_post();
							wp_set_object_terms( get_the_ID(), get_the_title(), $form_slug . '_attached_' . $attach_group_post_type );
						endwhile;









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
	public function remove_slug( $permalink, $post, $leavename ) {
		global $buddyforms, $bp;

		if ( ! isset( $buddyforms ) ) {
			return $permalink;
		}

		if ( ! defined( 'BP_VERSION' ) ) {
			return;
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
	public function theme_redirect() {

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
}
