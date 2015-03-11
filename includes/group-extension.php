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

            add_action( 'bp_actions', array($this, 'buddyforms_remove_group_admin_tab'), 9 );
            add_filter( 'buddyforms_user_can_edit', array($this, 'buddyforms_user_can_edit'), 10 );

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
	
					case 'before group activity' :
						add_action('bp_before_group_activity_post_form', array($this, 'display_post'), 1);
						break;
					case 'create a new tab' :
						$this->enable_nav_item = true;
						break;
						
				endswitch;
	
				$this->name					= $buddyforms['buddyforms'][$this->attached_form_slug]['singular_name'];
				$this->nav_item_position	= 20;
				$this->slug					= $buddyforms['buddyforms'][$this->attached_form_slug]['slug'];
				
			}

		}


        function buddyforms_front_js_loader_bp_groups_support($found){
            return true;
        }
        function buddyforms_user_can_edit($found){
            return true;
        }

        function buddyforms_remove_group_admin_tab() {
            if ( ! bp_is_group() || ! ( bp_is_current_action( 'admin' ) && bp_action_variable( 0 ) )) {
                return;
            }

            // Add the admin subnav slug you want to hide in the
            // following array
            $hide_tabs = array(
                'group-avatar' => 1,
                'delete-group' => 1,
            );

            $parent_nav_slug = bp_get_current_group_slug() . '_manage';

            // Remove the nav items
            foreach ( array_keys( $hide_tabs ) as $tab ) {
                bp_core_remove_subnav_item( $parent_nav_slug, $tab );
            }

            // You may want to be sure the user can't access
            if ( ! empty( $hide_tabs[ bp_action_variable( 0 ) ] ) ) {
                bp_core_add_message( __('Sorry buddy, but this part is restricted to super admins!', 'buddyforms'), 'error' );
                bp_core_redirect( bp_get_group_permalink( groups_get_current_group() ) );
            }
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
				'form_slug' => $form_slug,
			);

            echo buddyforms_create_edit_form($args);

		}

		function edit_screen_save($group_id = NULL){

            global $buddyforms, $form_slug;

            $form_slug			= $this->attached_form_slug;


            $args = array(
                'post_type' => $buddyforms['buddyforms'][$form_slug]['post_type'],
                'form_slug' => $form_slug,
            );

            echo buddyforms_create_edit_form($args);

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