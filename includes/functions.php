<?php

/**
 * Display featured image if group avatar is not set and group is attached with a post
 *
 * @package buddyforms
 * @since 1.0
 */
//add_filter( 'bp_get_group_avatar','bf_display_featured_image_as_group_avatar',1,1 );
function bf_display_featured_image_as_group_avatar($avatar){
    global $groups_template;

    $group_post_id	= groups_get_groupmeta(bp_get_group_id(), 'group_post_id');

    if(!isset($group_post_id))
        return $avatar;

    $image_id = get_post_thumbnail_id($group_post_id);
    $image_url = wp_get_attachment_image_src($image_id);

    if(!isset($image_url[0]))
        return $avatar;

    $avatar_size = 'width="150" height="150"';
    if(!bp_is_group_single())
        $avatar_size = 'width="50" height="50"';

    $avatar = '<img src="' . esc_url( $image_url[0] ) . '" class="avatar" alt="' . esc_attr( $groups_template->group->name ) . '" ' . $avatar_size . '/>';

    return $avatar;

}

/**
 * update post meta
 *
 * @package buddyforms
 * @since 1.0
 */
add_action('buddyforms_update_post_meta', 'bf_ge_updtae_post_meta', 99, 2);
function bf_ge_updtae_post_meta($customfield, $post_id){
	global $buddyforms;

	$post_type = get_post_type($post_id);
	$form_slug = get_post_meta($post_id, '_bf_form_slug', true);

	if(!isset($form_slug))
		return;
			  
	if( $customfield['type'] == 'attachgrouptype' ){

        $Attach_group_post_type = $buddyforms[$customfield['attachgrouptype']]['post_type'];

		$taxonomy = get_taxonomy($form_slug . '_attached_' . $Attach_group_post_type);
		if (isset($taxonomy->hierarchical) && $taxonomy->hierarchical == true)  {
			wp_set_post_terms( $post_id, $_POST[ $customfield['slug'] ], $form_slug . '_attached_' . $Attach_group_post_type, false );
		}
		
	}
	if( $customfield['slug'] == 'post_excerpt' ){
		$my_post = array(
            'ID'        		=> $post_id,
            'post_excerpt'		=> $_POST['post_excerpt'],
            'post_type' 		=> $post_type,
		);
          
		// Update the post
        $post_id = wp_update_post( $my_post );
	}

}

/**
 * Delete a Group
 *
 * @package buddyforms
 * @since 0.1-beta
 */
add_action('buddyforms_delete_post', 'buddyforms_delete_a_group');
function buddyforms_delete_a_group($post_id){
    $BuddyForms_GroupControl = new BuddyForms_GroupControl;
    $BuddyForms_GroupControl->delete_a_group($post_id);
}	

/**
 * Delete a post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_delete_a_group_post($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');
    wp_delete_post($groups_post_id);
}

add_action('groups_before_delete_group', 'buddyforms_delete_a_group_post');

/**
 * Update a product post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_group_header_fields_save($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');

    $my_post = array(
        'ID' => $groups_post_id,
        'post_title' => $_POST['group-name'],
        'post_content' => $_POST['group-desc']
    );

    // update the post
	$post_id = wp_update_post($my_post);
}
add_action('groups_group_details_edited', 'buddyforms_group_header_fields_save');


function buddyforms_groups_group_settings_edited($group_id){
    $groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');

    $group_status = $_POST['group-status'];

    $post_status = $group_status;

    if ( $group_status == 'hidden' )
        $post_status = 'draft';

    if ( $group_status == 'public' )
        $post_status = 'publish';

    $post_status = apply_filters( 'bf_attached_grouppost_post_status', $post_status, $group_status );

    $my_post = array(
        'ID' => $groups_post_id,
        'post_status' => $post_status
    );

    // update the post
    $post_id = wp_update_post($my_post);

}
add_action('groups_group_settings_edited','buddyforms_groups_group_settings_edited');


 /**
 * this function is a bit tricky and needs some fixing.
 * I have not found a way to overwrite the group home and use the new template system.
 * If someone can have a look into this one would be great!
 *
 * @author svenl77
 * @since 0.1
 *
 * @uses apply_filters()
 * @return string
 */

function buddyforms_groups_load_template_filter($found_template, $templates) {
	global $bp;
	
	if ($bp->current_component == BP_GROUPS_SLUG && $bp->current_action == 'home') {
		$templates = buddyforms_ge_locate_template('buddyforms/groups/groups-home.php');
		exit ;
	}

	return apply_filters('buddyforms_groups_load_template_filter', $found_template, $templates);
}

/**
 * Locate a template
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */
function buddyforms_ge_locate_template($file) {
	if (locate_template(array($file), false)) {
		locate_template(array($file), true);
	} else {
		include (BUDDYFORMS_GE_TEMPLATE_PATH . $file);
	}
}

/**
 * ShortCode/Template Tag
 *
 * Link to Attached Group
 *
 * @package Attach Posts to Groups Extension
 * @since 1.0.3
 */
add_shortcode('buddyforms_link_to_group','buddyforms_link_to_group');
function buddyforms_link_to_group(){

    $post_id = get_the_ID();
    $group_id = get_post_meta( $post_id, '_post_group_id', true );

    if(!isset($group_id))
        return;

    if(!function_exists('groups_get_group'))
        return;

    $group = groups_get_group( array( 'group_id' => $group_id ) );

    return trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/'  );

}

/**
 * ShortCode/Template Tag
 *
 * Link to Attached Post
 *
 * @package Attach Posts to Groups Extension
 * @since 1.0.3
 */
add_shortcode('buddyforms_link_to_post','buddyforms_link_to_post');
function buddyforms_link_to_post(){

    if(!function_exists('bp_get_group_id'))
        return;

    $group_id = bp_get_group_id();
    $post_id = groups_get_groupmeta( $group_id, 'group_post_id');

    if(!isset($post_id))
        return;

    return get_permalink($post_id);

}

add_filter('bf_form_before_render', 'attached_group_bf_form_before_render', 10, 2);
function attached_group_bf_form_before_render($form, $args){
    if(!bp_is_group())
        return $form;

    extract($args);
    ob_start();
    ?>
    <script>
        jQuery(document).ready(function(jQuery) {
            jQuery(<?php echo "'#_bp_group_edit_nonce_" . $form_slug . "'" ?>).appendTo(jQuery('.form-actions'));
            jQuery('#group-id').appendTo(jQuery('.form-actions'));
        });
    </script>
    <?php
    $groups_js = ob_get_contents();
    ob_end_clean();

    $form->addElement(new Element_HTML($groups_js));

    return $form;
}


add_action('buddyforms_groups_single_title', 'buddyforms_groups_single_title', 10, 2);
function buddyforms_groups_single_title($title, $args){
    global $buddyforms;

    if(!bp_is_group())
        return;

    extract($args);

    if(!(isset($buddyforms[$form_slug]['groups']['display_content']) && in_array('title', $buddyforms[$form_slug]['groups']['display_content'])))
        return;
    ?>

    <?php do_action('buddyforms_before_groups_single_title') ?>

    <div class="entry-title">
        <?php echo $title ?>
    </div>

<?php
}

add_action('buddyforms_groups_single_content', 'buddyforms_groups_single_content', 10, 2);
function buddyforms_groups_single_content($content, $args){
    global $buddyforms;

    if(!bp_is_group())
        return;

    extract($args);

    if(!(isset($buddyforms[$form_slug]['groups']['display_content']) && in_array('content', $buddyforms[$form_slug]['groups']['display_content'])))
        return;
    ?>

    <?php do_action('buddyforms_before_groups_single_content') ?>

    <div class="entry-single-content">
        <?php echo $content ?>
    </div>

    <?php do_action('buddyforms_after_groups_single_content') ?>

<?php
}

add_action('buddyforms_groups_single_post_meta', 'buddyforms_groups_single_post_meta', 10, 2);
function buddyforms_groups_single_post_meta($form_fields, $args){
    global $buddyforms;

    if(!bp_is_group())
        return;

    extract($args);

    if(bp_current_action() != $form_slug );
        return;

    if(!(isset($buddyforms[$form_slug]['groups']['display_content']) && in_array('meta', $buddyforms[$form_slug]['groups']['display_content'])))
        return;

    ?>
    <div class="entry-single-meta">

        <?php

        foreach ($form_fields as $key => $customfield) {

            if (empty($customfield['slug']) || $customfield['slug'] == 'editpost_title' || $customfield['slug'] == 'editpost_content') {
                continue;
            }

            $customfield_value = get_post_meta(get_the_ID(), $customfield['slug'], true);

            if (!empty($customfield_value)) {
                $post_meta_tmp = '<div class="post_meta ' . $customfield['slug'] . '">';
                $post_meta_tmp .= '<label>' . $customfield['name'] . '</label>';

                if (is_array($customfield_value)) {
                    $meta_tmp = "<p>" . implode(',', $customfield_value) . "</p>";
                } else {
                    $meta_tmp = "<p>" . $customfield_value . "</p>";
                }

                switch ($customfield['type']) {
                    case 'taxonomy':
                        $meta_tmp = get_the_term_list($post->ID, $customfield['taxonomy'], "<p>", ' - ', "</p>");
                        break;
                    case 'link':
                        $meta_tmp = "<p><a href='" . $customfield_value . "' " . $customfield['name'] . ">" . $customfield_value . " </a></p>";
                        break;
                    default:
                        apply_filters('buddyforms_form_element_display_frontend', $customfield);
                        break;
                }

                $post_meta_tmp .= $meta_tmp;
                $post_meta_tmp .= '</div>';

                echo apply_filters('buddyforms_group_single_post_meta_tmp', $post_meta_tmp);


            }
        }
        ?>

    </div>

<?php
}