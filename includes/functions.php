<?php 

function buddyforms_delete_a_group($post_id){
	BuddyForms_GroupControl::delete_a_group($post_id);
}
add_action('buddyforms_delete_post', 'buddyforms_delete_a_group');			

function groups_edit_form($args = Array()){
	global $bp;
	
	echo $bp->current_component;
	
	if($bp->current_component != 'groups')
		return $args;

	$groups_post_id = groups_get_groupmeta( bp_get_group_id(), 'group_post_id' );
	$posttype		= groups_get_groupmeta( bp_get_group_id(), 'group_type' );
	$the_post		= get_post( $groups_post_id );
	$post_id		= $the_post->ID;
	
	$args = Array(
		'posttype' => $posttype,
		'the_post' => $the_post,
		'post_id' => $post_id
	);

	return $args;
}
add_filter('buddyforms_create_edit_form_args','groups_edit_form',1,1);
 	


/**
 * Delete a post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_delete_product_post($group_id) {
	$groups_post_id = groups_get_groupmeta($group_id, 'group_post_id');

	wp_delete_post($groups_post_id);
}

add_action('groups_before_delete_group', 'buddyforms_delete_product_post');

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

function buddyforms_form_element_add_field_ge($form_fields_new, $post_type, $field_type, $field_id){
	global $buddyforms;
	if($field_type  == 'AttachGroupType')
		$form_fields_new[4] 	= new Element_Select("Attach Group Type:", "buddyforms_options[buddyforms][".$post_type."][form_fields][".$field_id."][AttachGroupType]", $buddyforms['selected_post_types'], array('value' => $value));
	return $form_fields_new;	
}
add_filter('buddyforms_form_element_add_field','buddyforms_form_element_add_field_ge',1,5);


function buddyforms_create_edit_form_display_element_group($form,$post_id,$posttype,$customfield,$customfield_val){
								
	if($customfield['type']  == 'AttachGroupType'){
		
		$attached_tax_name = $posttype . '_attached_' . $customfield['AttachGroupType'];
		$term_list = wp_get_post_terms($post_id, $attached_tax_name, array("fields" => "ids"));

		$args = array('multiple' => $customfield['multiple'], 'selected_cats' => $term_list, 'hide_empty' => 0, 'id' => $key, 'child_of' => 0, 'echo' => FALSE, 'selected' => false, 'hierarchical' => 1, 'name' => sanitize_title($customfield['name']) . '[]', 'class' => 'postform', 'depth' => 0, 'tab_index' => 0, 'taxonomy' => $attached_tax_name, 'hide_if_empty' => FALSE, );

		$dropdown = wp_dropdown_categories($args);

		if (is_array($customfield['multiple'])) {
			$dropdown = str_replace('id=', 'multiple="multiple" id=', $dropdown);
		}
		if (is_array($term_list)) {
			foreach ($term_list as $value) {
				$dropdown = str_replace(' value="' . $value . '"', ' value="' . $value . '" selected="selected"', $dropdown);
			}
		}

		$form->addElement(new Element_HTML($dropdown));
	}
	return $form;
	
}
add_filter('buddyforms_create_edit_form_display_element','buddyforms_create_edit_form_display_element_group',1,5);


function buddyforms_add_form_element_in_sidebar($form, $selected_post_types){
	
	if(bp_is_active('groups')){		
		$form->addElement(new Element_HTML('<p><a href="AttachGroupType/'.$selected_post_types.'" class="action">AttachGroupType</a></p>'));
	}
	return $form;
}
add_filter('buddyforms_add_form_element_in_sidebar','buddyforms_add_form_element_in_sidebar',1,2);


function buddyforms_admin_settings_sidebar_metabox($form, $selected_form_slug){
	global $buddyforms;
	
	$buddyforms_options = get_option('buddyforms_options');
	$buddyforms['hooks']['form_element'] = apply_filters('buddyforms_form_element_hooks',$buddyforms['hooks']['form_element'],$selected_form_slug);
	
	if(bp_is_active('groups')){						
		$form->addElement(new Element_HTML('
		<div class="accordion-group">
			<div class="accordion-heading"><p class="accordion-toggle" data-toggle="collapse" data-parent="#accordion_'.$selected_form_slug.'" href="#accordion_'.$selected_form_slug.'_group_options">Groups Control</p></div>
		    <div id="accordion_'.$selected_form_slug.'_group_options" class="accordion-body collapse">
				<div class="accordion-inner">')); 
					$form->addElement(new Element_HTML('<p>
					Here you can attach this post type to groups. Every time a new post is created a new group will be created too.<br>
					Important:<br>
					Post status will affect group privacy options.
				    draft = hidden<br>
				    publish = public<br>
					</p>'));
					
					$attache = '';					
					if(isset($buddyforms_options['buddyforms'][$selected_form_slug]['groups']['attache']))
						$attache = $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['attache'];
					

					
					$form->addElement(new Element_Checkbox("Attach to Group?", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][attache]", array("Yes. I want to create a group for each post of this post type and attach the post to the group."), array('value' => $attache)));
					$form->addElement(new Element_HTML('<br>'));
					$form->addElement(new Element_Select("Display Post: <p>the option \"replace home create new tab activity\" only works with a buddypress theme. </p>", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][display_post]", array(
					'nothing',
					'create a new tab', 
					'replace home new tab activity')
					,array('value' => $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['display_post'])));
					
					$form->addElement(new Element_HTML('<br><br><p>The title and content is displayed in the group header. If you want to display it somewere else, you can do it here but need to adjust the groups-header.php in your theme. If you want to hide it there.</p>'));
					$form->addElement( new Element_Select("Display Title:", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][title][display]", $buddyforms['hooks']['form_element'], array('value' => $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['title']['display'])));
					$form->addElement( new Element_Select("Display Content:", "buddyforms_options[buddyforms][".$selected_form_slug."][groups][content][display]", $buddyforms['hooks']['form_element'], array('value' => $buddyforms_options['buddyforms'][$selected_form_slug]['groups']['content']['display'])));
	
		$form->addElement(new Element_HTML('
				</div>
			</div>
		</div>'));	
	}				  
	return $form;
}	
add_filter('buddyforms_admin_settings_sidebar_metabox','buddyforms_admin_settings_sidebar_metabox',1,2);


function form_element_group_hooks($buddyforms_form_element_hooks,$form_slug){
	
	$buddyforms_options = get_option('buddyforms_options');
	
	if(bp_is_active('groups')){
		if(isset($buddyforms_options['buddyforms'][$form_slug]['groups']['attache'])){
			remove_filter( 'buddyforms_form_element_hooks', 'form_element_single_hooks' );
		
			array_push($buddyforms_form_element_hooks,
				'buddyforms_before_groups_single_title',
				'buddyforms_groups_single_title',
				'buddyforms_before_groups_single_content',
				'buddyforms_groups_single_content',
				'buddyforms_after_groups_single_content',
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
		
	}
	return $buddyforms_form_element_hooks;
}

add_filter('buddyforms_form_element_hooks','form_element_group_hooks',1,2);

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
?>