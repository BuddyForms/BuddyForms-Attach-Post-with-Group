<?php 

/**
 * Delete a product post
 *
 * @package CPT4BP
 * @since 0.1-beta
 */
function cpt4bp_delete_product_post($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');

	wp_delete_post($groups_post_id);
}

add_action('groups_before_delete_group', 'cpt4bp_delete_product_post');

/**
 * Update a product post
 *
 * @package CPT4BP
 * @since 0.1-beta
 */
function cpt4bp_group_header_fields_save($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');
	$posttype = groups_get_groupmeta($group_id, 'group_type');

	$my_post = array('ID' => $groups_post_id, 'post_title' => $_POST['group-name'], 'post_content' => $_POST['group-desc']);

	// update the new post
	$post_id = wp_update_post($my_post);
}
add_action('groups_group_details_edited', 'cpt4bp_group_header_fields_save');

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

function cpt4bp_groups_load_template_filter($found_template, $templates) {
	global $bp;
		echo $filtert_template;
	if ($bp->current_component == BP_GROUPS_SLUG && $bp->current_action == 'home') {
		$templates = cpt4bp_ge_locate_template('cpt4bp/bp/groups-home.php');
		exit ;
	}

	return apply_filters('cpt4bp_groups_load_template_filter', $found_template, $templates);
}

function cpt4bp_form_add_element($form_fields_new, $post_type, $field_type, $value){
	
	if($field_type  == 'test')
		$form_fields_new[4] 	= new Element_Textbox("Values: <smal>value 1, value 2, ... </smal>", "cpt4bp_options[bp_post_types][".$post_type."][form_fields][".$field_id."][Values]", array('value' => $cpt4bp_options['bp_post_types'][$post_args[1]][form_fields][$field_id][Values]));
		
	return $form_fields_new;	
}
add_filter('cpt4bp_form_add_element','cpt4bp_form_add_element',1,4);


function cpt4bp_form_display_element($form, $customfield, $customfield_val){
								
	if($customfield['type']  == 'test'){
		$element_attr = $customfield['required'] ? array('required' => true, 'value' => $customfield_val) : array('value' => $customfield_val);
		$form->addElement(new Element_Textbox($customfield['name'] . ':<p><smal>' . $customfield['description'] . '</smal></p>', sanitize_title($customfield['name']), $element_attr));
	}
	return $form;
	
}
add_filter('cpt4bp_form_display_element','cpt4bp_form_display_element',1,3);


function cpt4bp_add_form_element_in_sidebar($form, $selected_post_types){
	
	if(bp_is_active('groups')){		
		$form->addElement(new Element_HTML('<p><a href="AttachGroupType/'.$selected_post_types.'" class="action">AttachGroupType</a></p>'));
	}
	return $form;
}
add_filter('cpt4bp_add_form_element_in_sidebar','cpt4bp_add_form_element_in_sidebar',1,2);

function cpt4bp_add_form_element_in_sidebar_test($form, $selected_post_types){
	
		$form->addElement(new Element_HTML('<p><a href="test/'.$selected_post_types.'" class="action">Test</a></p>'));
	return $form;
}
add_filter('cpt4bp_add_form_element_in_sidebar','cpt4bp_add_form_element_in_sidebar_test',2,2);

function cpt4bp_admin_settings_form_post_type_sidebar($form, $selected_post_types){
	global $cpt4bp;
	
	$cpt4bp_options = get_option('cpt4bp_options');
	
	if(bp_is_active('groups')){						
		$form->addElement(new Element_HTML('
		<div class="accordion-group">
			<div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion_'.$selected_post_types.'" href="#accordion_'.$selected_post_types.'_group_options">Groups Control</a></div>
		    <div id="accordion_'.$selected_post_types.'_group_options" class="accordion-body collapse">
				<div class="accordion-inner">')); 
					$form->addElement(new Element_HTML('<p>
					Here you can attach this post type to groups. Every time a new post is created a new group will be created too.<br>
					Important:<br>
					Post status will affect group privacy options.
				    draft = hidden<br>
				    publish = public<br>
					</p>'));
					$form->addElement(new Element_Checkbox("Attach to Group?", "cpt4bp_options[bp_post_types][".$selected_post_types."][groups][attache]", array("Yes. I want to create a group for each post of this post type and attach the post to the group."), array('value' => $cpt4bp_options['bp_post_types'][$selected_post_types]['groups'][attache])));
					$form->addElement(new Element_HTML('<br>'));
					$form->addElement(new Element_Select("Display Post: <p>the option \"replace home create new tab activity\" only works with a buddypress theme. </p>", "cpt4bp_options[bp_post_types][".$selected_post_types."][groups][display_post]", array(
					'nothing',
					'create a new tab', 
					'replace home new tab activity')
					,array('value' => $cpt4bp_options['bp_post_types'][$selected_post_types]['groups'][display_post])));
					
					$form->addElement(new Element_HTML('<br><br><p>The title and content is displayed in the group header. If you want to display it somewere else, you can do it here but need to adjust the groups-header.php in your theme. If you want to hide it there.</p>'));
					$form->addElement( new Element_Select("Display Title:", "cpt4bp_options[bp_post_types][".$selected_post_types."][groups][title][display]", $cpt4bp[hooks][form_element], array('value' => $cpt4bp_options['bp_post_types'][$selected_post_types][groups]['title']['display'])));
					$form->addElement( new Element_Select("Display Content:", "cpt4bp_options[bp_post_types][".$selected_post_types."][groups][content][display]", $cpt4bp[hooks][form_element], array('value' => $cpt4bp_options['bp_post_types'][$selected_post_types][groups]['content']['display'])));
	
		$form->addElement(new Element_HTML('
				</div>
			</div>
		</div>'));	
	}				  
	return $form;
}	
add_filter('cpt4bp_admin_settings_form_post_type_sidebar','cpt4bp_admin_settings_form_post_type_sidebar',1,2);


function form_element_group_hooks($form_element_hooks){
	if(bp_is_active('groups')){
		array_push($form_element_hooks,
			'cpt4bp_before_groups_single_title',
			'cpt4bp_groups_single_title',
			'cpt4bp_before_groups_single_content',
			'cpt4bp_groups_single_content',
			'cpt4bp_after_groups_single_content',
			'bp_before_group_header',
			'bp_after_group_menu_admins',
			'bp_before_group_menu_mods',
			'bp_after_group_menu_mods', 
			'bp_before_group_header_meta',
			'bp_group_header_actions', 
			'bp_group_header_meta',
			'bp_after_group_header',
			'bp_before_group_activity_post_form',
			'bp_before_group_activity_content',
			'bp_after_group_activity_content'
		);
	}
	return $form_element_hooks;
}

add_filter('form_element_hooks','form_element_group_hooks');

/**
 * Locate a template
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */
function cpt4bp_ge_locate_template($file) {
	if (locate_template(array($file), false)) {
		locate_template(array($file), true);
	} else {
		include (CPT4BP_GE_TEMPLATE_PATH . $file);
	}
}
?>