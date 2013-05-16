<?php
if (class_exists('BP_Group_Extension')) :
	class buddyforms_Groups extends BP_Group_Extension {
		public $enable_create_step	= true;
		public $enable_nav_item		= false;
		public $enable_edit_item	= true;

		/**
		* Extends the group and register the nav item and add groupmeta to the $bp global
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function __construct() {
			global $bp, $buddyforms;

			/**
			 * @TODO Is this supposed to loop through everything and constantly replace the parameters?
			 */
			if (bp_has_groups()) :
				while (bp_groups()) : bp_the_group();
					$attached_post_id = groups_get_groupmeta(bp_get_group_id(), 'group_post_id');
					$attached_post_type = groups_get_groupmeta(bp_get_group_id(), 'group_type');
				endwhile;
			endif;

			if (!empty($buddyforms['bp_post_types'][$attached_post_type]['form_fields'])) {
				foreach ($buddyforms['bp_post_types'][$attached_post_type]['form_fields'] as $key => $customfield) :
					$customfield_value = get_post_meta($attached_post_id, sanitize_title($customfield['name']), true);
					if ($customfield_value != '' && $customfield['display'] != 'no') :
						$post_meta_tmp = '<div class="post_meta ' . sanitize_title($customfield['name']) . '">';
						$post_meta_tmp .= '<lable>' . $customfield['name'] . '</lable>';
						$post_meta_tmp .= "<p><a href='" . $customfield_value . "' " . $customfield['name'] . ">" . $customfield_value . " </a></p>";
						$post_meta_tmp .= '</div>';

						add_action($customfield['display'], create_function('', 'echo "' . addcslashes($post_meta_tmp, '"') . '";'));
					endif;
				endforeach;
			} else {
				$this->enable_edit_item	= false;
			}

			switch ($buddyforms['bp_post_types'][$attached_post_type]['groups']['display_post']) {

				case 'nothing' :
					add_action('bp_before_group_activity_post_form', array($this, 'display_post'), 1);
					break;
				case 'create a new tab' :
					$this->enable_nav_item = true;
					break;
				case 'replace home new tab activity' :
					add_filter('bp_located_template', 'buddyforms_groups_load_template_filter', 10, 2);
					$this->add_activity_tab();
					break;
			}

			if ($buddyforms['bp_post_types'][$attached_post_type][groups][title][display] != 'no') {
				add_action($buddyforms['bp_post_types'][$attached_post_type][groups][title][display], create_function('', 'echo "<div class=\"group_title\">' . get_the_title($attached_post_id) . '</div>";'));
			}
			if ($buddyforms['bp_post_types'][$attached_post_type][groups][content][display] != 'no') {
				add_action($buddyforms['bp_post_types'][$attached_post_type][groups][content][display], create_function('', 'echo "<div class=\"group_content\">' . get_post_field('post_content', $attached_post_id) . '</div>";'));
			}

			$this->name				= $buddyforms['bp_post_types'][$attached_post_type]['singular_name'];
			$this->nav_item_position	= 20;
			$this->slug				= $buddyforms['bp_post_types'][$attached_post_type]['slug'];

		}

		function display_post() {
			buddyforms_ge_locate_template('buddyforms/groups/single-post.php');
		}

		/**
		 * Display the edit screen
		 *
		 * @package buddyforms
		 * @since 0.1-beta
		 */
		public function edit_screen() {
			global $post;

			buddyforms_ge_locate_template('buddyforms/groups/edit-post.php');

			wp_nonce_field('groups_edit_save_' . $this->slug);
		}

		/**
		* Display or edit a Post
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function display() {
			global $bp, $wc_query;

			buddyforms_ge_locate_template('buddyforms/single-post.php');

		}

		/**
		* Add an activity tab
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function add_activity_tab() {
			global $bp;

			if (bp_is_group()) {
				bp_core_new_subnav_item(array('name' => 'Activity', 'slug' => 'activity', 'parent_slug' => $bp->groups->current_group->slug, 'parent_url' => bp_get_group_permalink($bp->groups->current_group), 'position' => 11, 'item_css_id' => 'nav-activity', 'screen_function' => create_function('', "bp_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );"), 'user_has_access' => 1));

				if (bp_is_current_action('activity')) {
					add_action('bp_template_content_header', create_function('', 'echo "' . esc_attr('Activity') . '";'));
					add_action('bp_template_title', create_function('', 'echo "' . esc_attr('Activity') . '";'));
				}
			}
		}

	}

	bp_register_group_extension('buddyforms_Groups');
endif;
