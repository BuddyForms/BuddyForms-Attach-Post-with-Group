<?php

/**
 * Display featured image if group avatar is not set and group is attached with a post
 *
 * @package buddyforms
 * @since 1.0
 */
add_filter( 'bp_get_group_avatar','bf_display_featured_image_as_group_avatar',1,1 );
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
		
	$post_type = get_post_type($post_id);
	$form_slug = get_post_meta($post_id, '_bf_form_slug', true);

	if(!isset($form_slug))
		return;
			  
	if( $customfield['type'] == 'AttachGroupType' ){
			
		$taxonomy = get_taxonomy($form_slug . '_attached_' . $customfield['AttachGroupType']);
		if (isset($taxonomy->hierarchical) && $taxonomy->hierarchical == true)  {
			wp_set_post_terms( $post_id, $_POST[ $customfield['slug'] ], $form_slug . '_attached_' . $customfield['AttachGroupType'], false );
		}
		
	}
	if( $customfield['slug'] == 'post_excerpt' ){
		$my_post = array(
            'ID'        		=> $post_id,
            'post_excerpt'		=> $_POST['post_excerpt'],
            'post_type' 		=> $post_type,
            'post_status' 		=> 'publish'
		);
          
		// Update the new post
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
	BuddyForms_GroupControl::delete_a_group($post_id);
}	

/**
 * Delete a post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_delete_product_post($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');

	$post = get_post($groups_post_id);


    wp_delete_post($groups_post_id);
}

//add_action('groups_before_delete_group', 'buddyforms_delete_product_post');

/**
 * Update a product post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_group_header_fields_save($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');
	$posttype = groups_get_groupmeta($group_id, 'group_type');

	$my_post = array('ID' => $groups_post_id, 'post_title' => $_POST['group-name'], 'post_content' => $_POST['group-desc']);

	// update the new post
	$post_id = wp_update_post($my_post);
}
add_action('groups_group_details_edited', 'buddyforms_group_header_fields_save');

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

function buddyforms_form_element_add_field_ge($form_fields, $form_slug, $field_type, $field_id){
	global $buddyforms;

	if($field_type != 'AttachGroupType')
		return $form_fields;	
 
	$AttachGroupType	= Array();
	
	$value = '';
	if(isset($buddyforms['buddyforms'][$form_slug]['form_fields'][$field_id]['AttachGroupType']))
		$value	= $buddyforms['buddyforms'][$form_slug]['form_fields'][$field_id]['AttachGroupType'];

	foreach ($buddyforms['buddyforms'] as $key => $buddyform) {
		
		if(isset($buddyform['groups']['attache']))
			$AttachGroupType[$key] = $buddyform['name'];
	
	}
	
	$form_fields['left']['AttachGroupType'] 	= new Element_Select('<b>' . __("Attach Group Type:", 'buddyforms'), "buddyforms_options[buddyforms][".$form_slug."][form_fields][".$field_id."][AttachGroupType]", $AttachGroupType, array('value' => $value));
	
	$multiple = 'false';
	if(isset($buddyforms_options['buddyforms'][$form_slug]['form_fields'][$field_id]['multiple']))
		$multiple = $buddyforms_options['buddyforms'][$form_slug]['form_fields'][$field_id]['multiple'];
		$form_fields['left']['multiple'] = new Element_Checkbox('',"buddyforms_options[buddyforms][".$form_slug."][form_fields][".$field_id."][multiple]",array('multiple' => '<b>' . __("Multiple:", 'buddyforms') . '</b>' ),array('value' => $multiple));
				
	return $form_fields;	
}
add_filter('buddyforms_form_element_add_field','buddyforms_form_element_add_field_ge',1,5);


function buddyforms_create_edit_form_display_element_group($form,$post_id,$form_slug,$customfield,$customfield_val){
								
	if($customfield['type']  == 'AttachGroupType'){
		
		$attached_tax_name = $form_slug . '_attached_' . $customfield['AttachGroupType'];
		$term_list = wp_get_post_terms($post_id, $attached_tax_name, array("fields" => "ids"));

		$multiple = '';
		if(isset($customfield['multiple']))
			$multiple = $customfield['multiple'];
		
		$args = array(
			'multiple' => $multiple,
			'selected_cats' => $term_list,
			'hide_empty' => 0,
			//'id' => $key,
			'child_of' => 0,
			'echo' => FALSE,
			'selected' => false,
			'hierarchical' => 1,
			'name' => sanitize_title($customfield['name']) . '[]',
			'class' => 'postform chosen',
			'depth' => 0,
			'tab_index' => 0,
			'taxonomy' => $attached_tax_name,
			'hide_if_empty' => FALSE,
            'show_option_none' => 'Nothing Selected'
		);

		$dropdown = wp_dropdown_categories($args);

		 if (isset($multiple) && is_array($multiple)) {
			 $dropdown = str_replace('id=', 'multiple="multiple" id=', $dropdown);
		 }
		if (is_array($term_list)) {
			foreach ($term_list as $value) {
				$dropdown = str_replace(' value="' . $value . '"', ' value="' . $value . '" selected="selected"', $dropdown);
			}
		}
		$element = new Element_HTML('<label>'.$customfield['name'] . ':</label><p><i>' . $customfield['description'] . '</i></p>');
		bf_add_element($form, $element);
					
		$element = new Element_HTML($dropdown);
		bf_add_element($form, $element);

		}
	return $form;
	
}
add_filter('buddyforms_create_edit_form_display_element','buddyforms_create_edit_form_display_element_group',1,5);

function buddyforms_add_form_element_to_sidebar($form, $form_slug){
	
	if(bp_is_active('groups')){		
		$form->addElement(new Element_HTML('<p><a href="AttachGroupType/'.$form_slug.'" class="action">AttachGroupType</a></p>'));
	}
	return $form;
}
add_filter('buddyforms_add_form_element_to_sidebar','buddyforms_add_form_element_to_sidebar',1,2);


function buddyforms_admin_settings_sidebar_metabox($form, $selected_form_slug){

	$buddyforms_options = get_option('buddyforms_options');

    if(bp_is_active('groups')){
		$form->addElement(new Element_HTML('
		<div class="accordion-group postbox">
			<div class="accordion-heading"><p class="accordion-toggle" data-toggle="collapse" data-parent="#accordion_'.$selected_form_slug.'" href="#accordion_'.$selected_form_slug.'_group_options">Groups Control</p></div>
		    <div id="accordion_'.$selected_form_slug.'_group_options" class="accordion-body collapse">
				<div class="accordion-inner">')); 
					$form->addElement(new Element_HTML('<p>
					Attach this form to groups. If a new post is created, a new group will be attached to the post.<br><br>
					<b>Important: </b>
					Post status will affect group privacy options.<br>
				    draft = hidden<br>
				    publish = public<br>
					</p>'));

                    $attache = '';
                    if(isset($buddyforms_options['buddyforms'][$selected_form_slug]['groups']['attache']))
                        $attache = $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['attache'];

                    $form->addElement(new Element_Checkbox("<b>Attach with Group</b>", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][attache]", array("create_group" => "Create a group for each post of this form."), array('value' => $attache)));

                    $form->addElement(new Element_HTML('<br>'));

                    $redirect = '';
                    if(isset($buddyforms_options['buddyforms'][$selected_form_slug]['groups']['redirect']))
                        $redirect = $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['redirect'];

                    $form->addElement(new Element_Checkbox("<b>Redirect to Group</b>", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][redirect]", array("redirect_group" => "Redirect the post to the group."), array('value' => $redirect)));

                    $form->addElement(new Element_HTML('<br>'));

					
					$display_post = '';
					if(isset($buddyforms_options['buddyforms'][$selected_form_slug]['groups']['display_post']))
						$display_post = $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['display_post'];
					
					$form->addElement(new Element_Select("<b>Display Post</b><p>the option \"replace home create new tab activity\" only works with a buddypress themes. </p>", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][display_post]", array(
					'nothing',
					'create a new tab', 
					'replace home new tab activity')
					,array('value' => $display_post)));

			$form->addElement(new Element_HTML('
				</div>
			</div>
		</div>'));	
	}				  
	return $form;
}	
add_filter('buddyforms_admin_settings_sidebar_metabox','buddyforms_admin_settings_sidebar_metabox',1,2);

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
?>