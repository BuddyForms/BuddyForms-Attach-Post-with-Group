<?php
if ( class_exists( 'BP_Group_Extension' ) ) {
	class BuddyForms_Groups extends BP_Group_Extension {
		public $enable_create_step = false;
		public $enable_nav_item = false;
		public $enable_edit_item = true;

		/**
		 * Extends the group and register the nav item and add groupmeta to the $bp global
		 *
		 * @package buddyforms
		 * @since 0.1-beta
		 */
		public function __construct() {
			global $buddyforms, $post_id, $form_slug;

			$this->attached_post_id    = groups_get_groupmeta( bp_get_current_group_id(), 'group_post_id' );
			$this->attached_post_type  = groups_get_groupmeta( bp_get_current_group_id(), 'group_type' );
			$this->attached_form_slug  = get_post_meta( $this->attached_post_id, '_bf_form_slug', true );
			$this->buddyforms_aptg     = groups_get_groupmeta( bp_get_current_group_id(), 'buddyforms-aptg' );
			$this->buddyforms_user_can = false;


			if ( isset( $this->buddyforms_aptg['can-create'] ) ) {
				switch ( $this->buddyforms_aptg['can-create'] ) {
					case 'admin' :
						if ( groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
							$this->buddyforms_user_can = true;
						}
						break;
					case 'mod' :
						if ( groups_is_user_mod( bp_loggedin_user_id(), bp_get_current_group_id() ) || groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
							$this->buddyforms_user_can = true;
						}
						break;
					case 'member' :
					default :
						if ( groups_is_user_member( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
							$this->buddyforms_user_can = true;
						}
						break;
				}
			}

			//add_action( 'bp_actions', array($this, 'buddyforms_remove_group_admin_tab'), 9 );

			if ( $this->buddyforms_user_can ) {
				add_filter( 'buddyforms_user_can_edit', array( $this, 'buddyforms_user_can_edit' ), 10 );
			}

			if ( $this->attached_form_slug ) {
				add_filter( 'buddyforms_front_js_css_loader', array(
					$this,
					'buddyforms_front_js_loader_bp_groups_support'
				) );
			}

			if ( isset( $buddyforms[ $this->attached_form_slug ]['revision'] ) ) {

				$form_slug = $this->attached_form_slug;
				$post_id   = $this->attached_post_id;

				add_action( 'bp_after_group_details_admin', function(){
				    global $post_id;
				    buddyforms_wp_list_post_revisions($post_id);
                } );
			}

			add_action( 'buddyforms_hook_fields_from_post_id', function () use ($post_id){
			    return $post_id;
            } );

			if ( ! isset( $buddyforms[ $this->attached_form_slug ]['form_fields'] ) ) {

				$this->enable_edit_item = false;
			}

			if ( isset( $this->attached_form_slug ) && isset( $buddyforms[ $this->attached_form_slug ]['groups']['display_post'] ) ) {


				switch ( $buddyforms[ $this->attached_form_slug ]['groups']['display_post'] ) :

					case 'before group activity' :
						add_action( 'bp_before_group_activity_post_form', array( $this, 'display_post' ), 1 );
						break;
					case 'create a new tab' :
						$this->enable_nav_item = true;
						break;

				endswitch;

				$this->name              = ! empty( $buddyforms[ $this->attached_form_slug ]['singular_name'] ) 
					? $buddyforms[ $this->attached_form_slug ]['singular_name'] 
					: $buddyforms[ $this->attached_form_slug ]['name'];
				$this->nav_item_position = 20;
				$this->slug              = $buddyforms[ $this->attached_form_slug ]['slug'];

			}

		}


		function buddyforms_front_js_loader_bp_groups_support( $found ) {
			return true;
		}
		/**
         * In this example we generally allow all to create and edit posts. This would be really dangerous and only should be used in closed intranets.
		 * In most situation you will use the filter to only fire in some situations. Please see the Groups Extension for a more complex example
		 * Link to the Groups Extension Example: https://github.com/BuddyForms/BuddyForms-Attach-Post-with-Group/blob/7d7d1b9c475cdfb076ef34963aeb94952a2e9545/includes/group-extension.php#L47
		 */
		function buddyforms_user_can_edit( $found ) {
			return true;
		}

		function buddyforms_remove_group_admin_tab() {
			if ( ! bp_is_group() || ! ( bp_is_current_action( 'admin' ) && bp_action_variable( 0 ) ) ) {
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
				bp_core_add_message( __( 'Sorry buddy, but this part is restricted to super admins!', 'buddyforms' ), 'error' );
				bp_core_redirect( bp_get_group_permalink( groups_get_current_group() ) );
			}
		}

		function display_post( $group_id = null ) {
			global $buddyforms, $form_slug;
			$attached_post_id = $this->attached_post_id;
			$form_slug        = $this->attached_form_slug;
			$group_permalink  = bp_get_group_permalink( groups_get_current_group() ) . bp_current_action();

			if ( apply_filters( 'bf_aptg_load_styles', true ) ) {
				wp_enqueue_style( 'bf-aptg-styles', plugins_url( 'assets/bf_aptg_styles.css', __FILE__ ) );
			}

			ob_start(); ?>
			<script>
				jQuery(function () {
					jQuery(".bf_show_aptg").click(function () {

						var url = window.location.href;
						var base_url = url.split("?")[0];

						if ( jQuery(this).attr("id") === "edit-post-details" ) {
							window.location.href = base_url + "?edit_post_group";
						} else {
							window.location.href = base_url;
						}

						return false;
					});

				});
			</script>

			<div class="bf_aptg_nav item-list-tabs no-ajax" id="subnav" role="navigation">
				<ul>
					<li id="view-post-details-groups-li" class="<?php echo ! isset( $_GET['edit_post_group'] ) ? "current" : "" ?> "><a id="view-post-details" class="bf_show_aptg"
					                                                        target="1"
					                                                        href=" <?php $group_permalink ?>">View</a>
					</li>

					<?php if ( $this->buddyforms_user_can ) { ?>
						<li id="edit-post-details-groups-li" class="<?php echo isset( $_GET['edit_post_group'] ) ? "current" : "" ?>"><a id="edit-post-details" class="bf_show_aptg" target="2"
						                                        href="<?php get_edit_post_link( $attached_post_id ) ?>">Edit</a>
						</li>
					<?php } ?>
				</ul>
			</div>

			<?php if ( isset( $_GET['edit_post_group'] ) && $this->buddyforms_user_can ) : ?>

			<div id="bf_aptg2" class="bf_main_aptg">
				<?php
				$args = array(
					'post_type' => $buddyforms[ $form_slug ]['post_type'],
					'post_id'   => $attached_post_id,
					'form_slug' => $form_slug,
				);

				echo buddyforms_create_edit_form( $args );
				?>
			</div>

			<?php else : ?>

			<div id="bf_aptg1" class="bf_main_aptg">
				<?php buddyforms_ge_locate_template( 'buddyforms/groups/single-post.php' ) ?>
			</div>

			<?php endif; ?>

			<?php
			$tmp = ob_get_clean();
			echo $tmp;
		}

		/**
		 * Display the edit screen
		 *
		 * @package buddyforms
		 * @since 0.1-beta
		 */
		public function edit_screen( $group_id = null ) {

			$form_slug = $this->attached_form_slug;
			$settings  = groups_get_groupmeta( $group_id, 'buddyforms-aptg' );

			$can_create = empty( $settings['can-create'] ) ? false : $settings['can-create'];

			?>

			<h2><?php echo $this->name; ?><?php _e( 'options', 'buddyforms' ) ?></h2>

			<div id="group-bf-aptg-options" <?php if ( $form_slug ) : ?>class="hidden"<?php endif ?>>

				<table class="group-bf-aptg-options">
					<tr>
						<td class="label">
							<label
								for="buddyforms-aptg-can-create"><?php _e( 'Minimum role to edit the attached post:', 'buddyforms' ) ?></label>
						</td>

						<td>
							<select name="buddyforms-aptg[can-create]" id="buddyforms-aptg-can-create">
								<option value="admin" <?php selected( $can_create, 'admin' ) ?> />
								<?php _e( 'Group admin', 'buddyforms' ) ?></option>
								<option value="mod" <?php selected( $can_create, 'mod' ) ?> />
								<?php _e( 'Group moderator', 'buddyforms' ) ?></option>
								<option value="member" <?php selected( $can_create, 'member' ) ?> />
								<?php _e( 'Group member', 'buddyforms' ) ?></option>
							</select>
						</td>
					</tr>

				</table>
			</div>
			<?php
		}

		function edit_screen_save( $group_id = null ) {
			$success = false;

			if ( ! $group_id ) {
				$group_id = $this->maybe_group_id;
			}

			$settings = ! empty( $_POST['buddyforms-aptg'] ) ? $_POST['buddyforms-aptg'] : array();

			if ( groups_update_groupmeta( $group_id, 'buddyforms-aptg', $settings ) ) {
				$success = true;
			}

			return $success;
		}

		/**
		 * Display or edit a Post
		 *
		 * @package buddyforms
		 * @since 0.1-beta
		 */
		public function display( $group_id = null ) {

			$this->display_post( $group_id );
		}

		/**
		 * Add an activity tab
		 *
		 * @package buddyforms
		 * @since 0.1-beta
		 */
		public function add_activity_tab() {
			global $bp;

			if ( bp_is_group() ) {
				bp_core_new_subnav_item( array(
					'name'            => 'Activity',
					'slug'            => 'activity',
					'parent_slug'     => $bp->groups->current_group->slug,
					'parent_url'      => bp_get_group_permalink( $bp->groups->current_group ),
					'position'        => 11,
					'item_css_id'     => 'nav-activity',
					'screen_function' => function(){
					    bp_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
                    },
					'user_has_access' => 1
				) );

				if ( bp_is_current_action( 'activity' ) ) {
					add_action( 'bp_template_content_header', function(){
					    _e( 'Activity', 'buddyforms' );
                    } );
					add_action( 'bp_template_title', function(){
					    _e( 'Activity', 'buddyforms' );
                    } );
				}
			}
		}

	}

	bp_register_group_extension( 'BuddyForms_Groups' );
}