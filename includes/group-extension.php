<?php
if (class_exists('BP_Group_Extension')) :
	class BuddyForms_Groups extends BP_Group_Extension {
		public $enable_create_step	= false;
		public $enable_nav_item		= false;
		public $enable_edit_item	= true;

		/**
		* Extends the group and register the nav item and add groupmeta to the $bp global
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function __construct() {
			global $buddyforms, $post_id, $form_slug;




			$this->attached_post_id		= groups_get_groupmeta( bp_get_current_group_id(), 'group_post_id');
			$this->attached_post_type	= groups_get_groupmeta( bp_get_current_group_id(), 'group_type');
			$this->attached_form_slug	= get_post_meta($this->attached_post_id, '_bf_form_slug', true);


            if($this->attached_form_slug)
                add_filter('buddyforms_front_js_css_loader', array($this, 'buddyforms_front_js_loader_bp_groups_support'));


			if(isset($buddyforms['buddyforms'][$this->attached_form_slug]['revision'])){
					
				$form_slug	= $this->attached_form_slug;
				$post_id	= $this->attached_post_id;
				
				add_action( 'bp_after_group_details_admin',  create_function('', 'global $post_id; buddyforms_wp_list_post_revisions($post_id);'));
			
			 }
			
			add_action('buddyforms_hook_fields_from_post_id', create_function('', 'return "' . $this->attached_post_id . '";'));

			if ( !isset( $buddyforms['buddyforms'][$this->attached_form_slug]['form_fields'] ) ){

				$this->enable_edit_item	= false;
			}
			
	
			if( isset($this->attached_form_slug) && isset($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['display_post'])){

				
				switch ($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['display_post']) :
	
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
						
				endswitch;
	
				if ( isset( $buddyforms['buddyforms'][$this->attached_form_slug]['groups']['title']['display'] ) && $buddyforms['buddyforms'][$this->attached_form_slug]['groups']['title']['display'] != 'no')
					add_action($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['title']['display'], create_function('', 'echo "<div class=\"group_title\">' . get_the_title($this->attached_post_id) . '</div>";'));
				
				
				if ( isset( $buddyforms['buddyforms'][$this->attached_form_slug]['groups']['content']['display'] ) && $buddyforms['buddyforms'][$this->attached_form_slug]['groups']['content']['display'] != 'no')
					add_action($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['content']['display'], create_function('', 'echo "<div class=\"group_content\">' . get_post_field('post_content', $this->attached_post_id) . '</div>";'));

	
				$this->name					= $buddyforms['buddyforms'][$this->attached_form_slug]['singular_name'];
				$this->nav_item_position	= 20;
				$this->slug					= $buddyforms['buddyforms'][$this->attached_form_slug]['slug'];
				
			}

		}


        function buddyforms_front_js_loader_bp_groups_support($found){
            return true;
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
		public function edit_screen($group_id = NULL) {
			global $buddyforms, $form_slug;
			
			$form_slug			= $this->attached_form_slug;
			$attached_post_id	= $this->attached_post_id;


			$args = array(
				'post_type' => $buddyforms['buddyforms'][$form_slug]['post_type'],
				'post_id' => $attached_post_id,
				'revision_id' => false,
				'form_slug' => $form_slug,
			);

            echo buddyforms_create_edit_form_shortcode($args);

		}
		
		function edit_screen_save($group_id = NULL){
			global $buddyforms;

			$form_slug			= $this->attached_form_slug;
			$post_type			= $this->attached_post_type;
			$attached_post_id	= $this->attached_post_id;

			$customfields		= $buddyforms['buddyforms'][$form_slug]['form_fields'];

			bf_update_post_meta($attached_post_id, $customfields);

			bp_core_add_message( "Post successfully updated." );
		}
		
		/**
		* Display or edit a Post
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function display($group_id = NULL) {
			$group_id = bp_get_group_id();
			buddyforms_ge_locate_template('buddyforms/groups/single-post.php');

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

    bp_register_group_extension('BuddyForms_Groups');
endif;