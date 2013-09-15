<?php
if (class_exists('BP_Group_Extension')) :
	class buddyforms_Groups extends BP_Group_Extension {
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
			global $bp, $buddyforms;
			

			// $group = groups_get_group( array( 'group_id' => bp_get_current_group_id() ) );
			
			//unset( $bp->groups->group_creation_steps );
			// echo '<pre>';
			// print_r( $bp);
			// echo '</pre>';
			
			// if(bp_get_group_has_avatar()){
				// echo 'ja mann';
			// }
			 /* Fetch the avatar from the folder, if not provide backwards compat. */
 
			//if ( !$avatar = bp_core_fetch_avatar( array( 'item_id' => bp_get_current_group_id()) ))
 			
			
			$this->attached_post_id		= groups_get_groupmeta( bp_get_current_group_id(), 'group_post_id');
			$this->attached_post_type	= groups_get_groupmeta( bp_get_current_group_id(), 'group_type');
			$this->attached_form_slug	= get_post_meta($this->attached_post_id, '_bf_form_slug', true);
			
			add_filter('buddyforms_user_can_edit', array($this, 'buddyforms_user_can_edit'),1);
			
			//add_filter('buddyforms_wp_editor', array($this, 'buddyforms_wp_editor_hidden'),1);
			
			if ( isset( $buddyforms['buddyforms'][$this->attached_form_slug]['form_fields'] ) ){
				foreach ($buddyforms['buddyforms'][$this->attached_form_slug]['form_fields'] as $key => $form_field) :
					
					$form_field_value = get_post_meta($this->attached_post_id, sanitize_title($form_field['name']), true);
						
					if ($form_field_value != '' && $form_field['display'] != 'no') :
						$post_meta_tmp = '<div class="post_meta ' . sanitize_title($form_field['name']) . '">';
						$post_meta_tmp .= '<lable>' . $form_field['name'] . '</lable>';
						$post_meta_tmp .= "<p><a href='" . $form_field_value . "' " . $form_field['name'] . ">" . $form_field_value . " </a></p>";
						$post_meta_tmp .= '</div>';
						add_action($form_field['display'], create_function('', 'echo "' . addcslashes($post_meta_tmp, '"') . '";'));
					endif;
		
					
				endforeach;
				
			} else {
				$this->enable_edit_item	= false;
			}
			
	
			if( isset($this->attached_form_slug) && isset($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['display_post'])){

				
				switch ($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['display_post']) {
	
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
	
				if ($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['title']['display'] != 'no') {
					add_action($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['title']['display'], create_function('', 'echo "<div class=\"group_title\">' . get_the_title($this->attached_post_id) . '</div>";'));
				}
				if ($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['content']['display'] != 'no') {
					add_action($buddyforms['buddyforms'][$this->attached_form_slug]['groups']['content']['display'], create_function('', 'echo "<div class=\"group_content\">' . get_post_field('post_content', $this->attached_post_id) . '</div>";'));
				}
	
				$this->name					= $buddyforms['buddyforms'][$this->attached_form_slug]['singular_name'];
				$this->nav_item_position	= 20;
				$this->slug					= $buddyforms['buddyforms'][$this->attached_form_slug]['slug'];
				
				// if(isset($this->attached_post_id))
					// add_filter('bp_get_group_avatar',array($this, 'display_avatar'), 1, 1);
			}

		}

		function buddyforms_user_can_edit($user_can_edit){
			//if(groups_is_user_member( bp_displayed_user_id(), bp_get_current_group_id() ) )  // this is a extra security check but for some reason it does not work...
				return true;
		}
		function buddyforms_wp_editor_hidden($wp_editor){
			return '';
		}
		function display_avatar($avatar){
			
			
			$dom = new DOMDocument();
			$dom->loadHTML($avatar);
			$src = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
			$src_headers = @get_headers($src);
			if($src_headers[0] == 'HTTP/1.1 200 OK') 
				return $avatar;
			
			if(has_post_thumbnail($this->attached_post_id)){
				$avatar = get_the_post_thumbnail($this->attached_post_id, array(150,150),array('class' => 'avatar'));	
			}
			
			return $avatar;
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
			global $buddyforms;
			
			$form_slug = $this->attached_form_slug;
			$attached_post_id = $this->attached_post_id;
			$customfields = $buddyforms['buddyforms'][$form_slug]['form_fields'];
			
			// if post edit screen is displayed
			wp_enqueue_style('the-form-css', plugins_url('css/the-form.css', __FILE__));	
			
			bf_post_meta('', $attached_post_id, $customfields);	
			
		}
		
		function edit_screen_save($group_id){
			global $buddyforms;
			
			$form_slug = $this->attached_form_slug;
			$attached_post_id = $this->attached_post_id;
			$customfields = $buddyforms['buddyforms'][$form_slug]['form_fields'];
			
			bf_update_post_meta($attached_post_id, $customfields);
			//groups_update_groupmeta( bp_get_current_group_id(), 'bf_post_meta' . bp_get_current_group_id(), $customfields );
			add_action('template_notices', create_function('', 'echo "<div id=\"message\" class=\"bp-template-notice updated\">

			<p>Post successfully updated.</p>

		</div>";'));

			
			
			
		}
		/**
		* Display or edit a Post
		*
		* @package buddyforms
		* @since 0.1-beta
		*/
		public function display() {
			global $bp, $wc_query;

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

	bp_register_group_extension('buddyforms_Groups');
endif;