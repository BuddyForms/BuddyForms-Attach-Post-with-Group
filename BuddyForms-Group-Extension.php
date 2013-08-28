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

		add_action('init'				, array($this, 'includes'));
		add_action('init'				, array($this, 'load_plugin_textdomain'), 10, 1);
		add_action('init'				, array($this, 'register_taxonomy'), 10, 2);
		add_action('bp_init'			, array($this, 'setup_group_extension'), 10, 1);
		add_action('template_redirect'	, array($this, 'theme_redirect'), 1, 2);
		
		add_filter('post_type_link'		, array($this, 'remove_slug'), 10, 3);

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
		do_action('buddyforms_ge_init');
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
		
		if (!defined('BUDDYFORMS_GE_INSTALL_PATH'))
			define('BUDDYFORMS_GE_INSTALL_PATH', dirname(__FILE__) . '/');

		if (!defined('BUDDYFORMS_GE_INCLUDES_PATH'))
			define('BUDDYFORMS_GE_INCLUDES_PATH', BUDDYFORMS_GE_INSTALL_PATH . 'includes/');

		if (!defined('BUDDYFORMS_GE_TEMPLATE_PATH'))
			define('BUDDYFORMS_GE_TEMPLATE_PATH', BUDDYFORMS_GE_INCLUDES_PATH . 'templates/');
		
	}

	/**
	 * Includes files needed by buddyforms
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function includes() {
		
		require_once (BUDDYFORMS_GE_INCLUDES_PATH . 'group-control.php');
		require_once (BUDDYFORMS_GE_INCLUDES_PATH . 'functions.php');

		}

	/**
	 * Loads the textdomain for the plugin
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */

	public function load_plugin_textdomain() {
		
		load_plugin_textdomain('buddyforms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	
	}

	/**
	 * Load the group extension file
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function setup_group_extension() {

		require_once (BUDDYFORMS_GE_INCLUDES_PATH . 'group-extension.php');

	}

	/**
	 * Registers BuddyPress buddyforms taxonomies for AttachGroupTypes
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function register_taxonomy() {
		global $buddyforms;

		if (!isset($buddyforms['buddyforms']))
			return;

		foreach ($buddyforms['buddyforms'] as $key => $buddyform) :
			if (isset($buddyform['form_fields'])) {
				foreach ($buddyform['form_fields'] as $key => $form_field) {

					if ($form_field['type'] == 'AttachGroupType') {

						$labels_group_groups = array('name' => sprintf(__('%s Categories'), $form_field['name']), 'singular_name' => sprintf(__('%s Category'), $form_field['name']), );

						register_taxonomy($post_type . '_attached_' . $form_field['AttachGroupType'], $post_type, array('hierarchical' => true, 'labels' => $labels_group_groups, 'show_ui' => true, 'query_var' => true, 'rewrite' => array('slug' => $post_type . '_attached_' . $form_field['AttachGroupType']), 'show_in_nav_menus' => false, ));

						$args = array('post_type' => $form_field['AttachGroupType'], // my custom post type
						'posts_per_page'	=> -1, // show all posts
						'post_status'		=> 'publish');

						$attached_posts = new WP_Query($args);

						while ($attached_posts->have_posts()) :
							$attached_posts->the_post();
							wp_set_object_terms(get_the_ID(), get_the_title(), $post_type . '_attached_' . $form_field['AttachGroupType']);
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
	public function remove_slug($permalink, $post, $leavename) {
		global $buddyforms, $bp;

		if (!isset($buddyforms['buddyforms']))
			return $permalink;

		if(!bp_is_active('groups'))
			return $permalink;
		
		$post_group_id	= get_post_meta($post->ID, '_post_group_id', true);
		$bf_form_slug	= get_post_meta($post->ID, '_bf_form_slug', true);
		
		$group_post_id	= groups_get_groupmeta($post_group_id, 'group_post_id');
		
		if ($post->ID != $group_post_id)
			return $permalink;

		if (!isset($buddyforms['buddyforms'][$bf_form_slug]['groups']['attache']))
			return $permalink;

		foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
			if (isset($buddyform['groups']['attache']))
				$permalink = str_replace(get_bloginfo('url') . '/' . $buddyform['post_type'], get_bloginfo('url') . '/' . $bp->groups->root_slug, $permalink);
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
		global $wp_query, $buddyforms, $bp;

		if (!isset($buddyforms['buddyforms']))
			return;

		if(!bp_is_active('groups'))
			return;

		if(!isset($wp_query->post_ID))
			return;
		
		$post_id = $wp_query->post_ID;
		$bf_form_slug = get_post_meta($post_id, '_bf_form_slug', true);
		
		if (!isset($buddyforms['buddyforms'][$bf_form_slug]['groups']['attache']))
			 return;

		$post_group_id = get_post_meta($post_id, '_post_group_id', true);
		$group_post_id = groups_get_groupmeta($post_group_id, 'group_post_id');

		if ($post_id != $group_post_id)
			return;

		//A Specific Custom Post Type redirect to the atached group
		if ( $wp_query->query_vars['post_type'] ==  $buddyforms['buddyforms'][$bf_form_slug]['post_type'] ) {

			if (is_singular()) {
				$link = get_bloginfo('url') . '/' . BP_GROUPS_SLUG . '/' . get_post_meta($wp_query->post->ID, '_link_to_group', true);
				wp_redirect($link, '301');
				exit ;
			}

		}

	}

	/**
	 * Perform the redirect
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public static function do_theme_redirect($url) {
		global $wp_query;
		if (have_posts()) {
			include ($url);
			die();
		} else {
			$wp_query->is_404 = true;
		}
	}

}