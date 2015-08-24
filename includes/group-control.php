<?php
class BuddyForms_GroupControl {

	/**
	 * Initiate the class
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function __construct() {
		
		if(is_admin()){
			add_action('save_post', array($this, 'create_a_group'), 99, 2);
		} else {
			add_action('buddyforms_after_save_post', array($this, 'create_a_group'), 10, 2);
		}
		
		add_action('wp_trash_post', array($this, 'delete_a_group'), 10, 1);
	}

	/**
	 * Creates a group if a group associated post is created
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function create_a_group($post_ID) {
		global $buddyforms;
		
		$post = get_post($post_ID);

        // First create/update the group if the post gets published.
        if ( !($post->post_status == 'publish' || $post->post_status == 'private') )
            return;

		// make sure we get the correct data
		if ($post->post_type == 'revision')
			$post = get_post($post->post_parent);

		$form_slug = get_post_meta($post->ID,'_bf_form_slug', true);
		
		if (!isset($form_slug))
			return;
		
		if (!isset($buddyforms['buddyforms']))
			return;

		if (!isset($buddyforms['buddyforms'][$form_slug]['groups']['attache'][0]))
			return;

		if (!class_exists('BP_Groups_Group'))
			return;

		if ($post->post_type != $buddyforms['buddyforms'][$form_slug]['post_type']) 
			return;
			
        $post_group_id = get_post_meta($post->ID, '_post_group_id', true);


        if( empty($post_group_id) ){
            $the_group = new BP_Groups_Group();
        } else {
            $the_group = groups_get_group( array( 'group_id' => $post_group_id ) );
        }

        $old_group_status = $the_group->status;

        $the_group->status = 'hidden';

        if ( $post->post_status == 'draft' || $post->post_status == 'pending' || $post->post_status == 'trash' )
            $the_group->status = 'hidden';

        if ( $post->post_status == 'publish' )
            $the_group->status = 'public';

        if ( $post->post_status == 'private' )
            $the_group->status = 'private';

        $the_group->status = apply_filters( 'bf_attached_group_status', $the_group->status, $old_group_status, $post->post_status );

        $the_group->creator_id = $post->post_author;
        $the_group->admins = $post->post_author;
        $the_group->name = $post->post_title;
        $the_group->slug = $post->post_name;

        $the_group->description = !empty($post->post_excerpt) ? $post->post_excerpt : $post->post_content;

        $the_group->is_invitation_only = 1;
        $the_group->enable_forum = 0;
        $the_group->date_created = current_time('mysql');
        $the_group->total_member_count = 1;

        $the_group = apply_filters( 'bf_attached_group_save', $the_group );

        $the_group->save();

        update_post_meta($post->ID, '_post_group_id', $the_group->id);
        update_post_meta($post->ID, '_link_to_group', $the_group->slug);

        groups_update_groupmeta($the_group->id, 'total_member_count', 1);
        groups_update_groupmeta($the_group->id, 'last_activity', time());
        groups_update_groupmeta($the_group->id, 'theme', 'buddypress');
        groups_update_groupmeta($the_group->id, 'stylesheet', 'buddypress');
        groups_update_groupmeta($the_group->id, 'group_post_id', $post->ID);
        groups_update_groupmeta($the_group->id, 'group_type', $post->post_type);

		$minimum_user_role = 'admin';
		if(isset($buddyforms['buddyforms'][$form_slug]['groups']['minimum_user_role']))
			$minimum_user_role = $buddyforms['buddyforms'][$form_slug]['groups']['minimum_user_role'];

		$settings = apply_filters( 'buddyforms_aptg_default_group_settings', array(
			'can-create' 	=> $minimum_user_role
		) );

		groups_update_groupmeta($the_group->id, 'buddyforms-aptg', $settings );

		self::add_member_to_group($the_group->id, $post->post_author);
	}

	/**
	 * Deletes a group if a group associated post is deleted
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public function delete_a_group($post_id) {

		$post = get_post($post_id);
		$post_group_id = get_post_meta($post->ID, '_post_group_id', true);
		
		//$terms = wp_get_object_terms($post->ID, 'product');
		//wp_remove_object_terms( $post_group_id, $terms, $taxonomy );
		
		if (!empty($post_group_id))
			groups_delete_group($post_group_id);

	}

	/**
	 * Add member to group as admin
	 * credidts go to boon georges. This function is coppyed from the group management plugin.
	 *
	 * @package buddyforms
	 * @since 0.1-beta
	 */
	public static function add_member_to_group($group_id, $user_id = false) {
		global $bp;

		if (!$user_id)
			$user_id = $bp->loggedin_user->id;

		/* Check if the user has an outstanding invite, is so delete it. */
		if (groups_check_user_has_invite($user_id, $group_id))
			groups_delete_invite($user_id, $group_id);

		/* Check if the user has an outstanding request, is so delete it. */
		if (groups_check_for_membership_request($user_id, $group_id))
			groups_delete_membership_request($user_id, $group_id);

		/* User is already a member, just return true */
		if (groups_is_user_member($user_id, $group_id))
			return true;

		if (!$bp->groups->current_group)
			$bp->groups->current_group = new BP_Groups_Group($group_id);

		$new_member = new BP_Groups_Member;
		$new_member->group_id = $group_id;
		$new_member->user_id = $user_id;
		$new_member->inviter_id = 0;
		$new_member->is_admin = 1;
		$new_member->user_title = '';
		$new_member->date_modified = gmdate("Y-m-d H:i:s");
		$new_member->is_confirmed = 1;

		if (!$new_member->save())
			return false;

		/* Record this in activity streams */
		groups_record_activity(array('user_id' => $user_id, 'action' => apply_filters('groups_activity_joined_group', sprintf(__('%s joined the group %s', 'buddyforms'), bp_core_get_userlink($user_id), '<a href="' . bp_get_group_permalink($bp->groups->current_group) . '">' . esc_html($bp->groups->current_group->name) . '</a>')), 'type' => 'joined_group', 'item_id' => $group_id));

		/* Modify group meta */
		groups_update_groupmeta($group_id, 'total_member_count', (int) groups_get_groupmeta($group_id, 'total_member_count') + 1);
		groups_update_groupmeta($group_id, 'last_activity', gmdate("Y-m-d H:i:s"));

		do_action('groups_join_group', $group_id, $user_id);

		return true;
	}

}
add_action('buddyforms_init', new BuddyForms_GroupControl() );
?>