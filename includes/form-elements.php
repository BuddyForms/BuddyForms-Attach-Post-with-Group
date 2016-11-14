<?php

function buddyforms_apwg_form_element_select_option( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$elements_select_options['buddypress']['label'] = 'BuddyPress';
	$elements_select_options['buddypress']['class'] = 'bf_show_if_f_type_post';
	$elements_select_options['buddypress']['fields']['apwg_taxonomy'] = array(
		'label'     => __( 'APWG Taxonomy', 'buddyforms' ),
		'unique'    => 'unique'
	);

	return $elements_select_options;
}

add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_apwg_form_element_select_option', 1, 2 );


function buddyforms_apwg_admin_settings_metabox() {
	add_meta_box( 'buddyforms_apwg', __( "BP Attach Post with Group", 'buddyforms' ), 'buddyforms_apwg_admin_settings_metabox_html', 'buddyforms', 'normal', 'low' );
	add_filter('postbox_classes_buddyforms_buddyforms_apwg','buddyforms_metabox_class');
	add_filter('postbox_classes_buddyforms_buddyforms_apwg','buddyforms_metabox_show_if_form_type_post');
	add_filter('postbox_classes_buddyforms_buddyforms_apwg','buddyforms_metabox_show_if_post_type_none');
}


function buddyforms_apwg_admin_settings_metabox_html( $form, $selected_form_slug ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$buddyform = get_post_meta( get_the_ID(), '_buddyforms_options', true );


	$form_setup = array();

	if ( bp_is_active( 'groups' ) ) {

		$form_setup[] = new Element_HTML( '<p>
        ' . __( 'Attach this form to groups. If a new post is created, a new group will be attached to the post.)', 'buddyforms' ) . '<br><br>
        <b>' . __( 'Important: ', 'buddyforms' ) . '</b>
        ' . __( 'Post status will effect group privacy options:', 'buddyforms' ) . '<br>
        ' . __( 'draft = hidden', 'buddyforms' ) . '<br>
        ' . __( 'publish = public', 'buddyforms' ) . '<br>
        </p>' );

		$attache = '';
		if ( isset( $buddyform['groups']['attache'] ) ) {
			$attache = $buddyform['groups']['attache'];
		}
		$form_setup[] = new Element_Checkbox( "<b>" . __( 'Attach with Group', 'buddyforms' ) . "</b>", "buddyforms_options[groups][attache]", array( "create_group" => __( 'Create a group for each post of this form.', 'buddyforms' ) ), array( 'value' => $attache ) );


		$minimum_user_role = '';
		if ( isset( $buddyform['groups']['minimum_user_role'] ) ) {
			$minimum_user_role = $buddyform['groups']['minimum_user_role'];
		}

		$form_setup[] = new Element_Select( "<b>" . __( 'Minimum User Role', 'buddyforms' ) . "</b>", "buddyforms_options[groups][minimum_user_role]", array(
				'admin'  => 'Group Admin',
				'mod'    => 'Group Moderator',
				'member' => 'Group member'
			)
			, array( 'value' => $minimum_user_role,
					'shortDesc' => __( "Select the minimum group role a user needs to edit the post", 'buddyforms' )
			) );

		$redirect = '';
		if ( isset( $buddyform['groups']['redirect'] ) ) {
			$redirect = $buddyform['groups']['redirect'];
		}

		$form_setup[] = new Element_Checkbox( "<b>" . __( 'Redirect to Group', 'buddyforms' ) . "</b>", "buddyforms_options[groups][redirect]", array( "redirect_group" => __( 'Redirect the post to the group.', 'buddyforms' ) ), array( 'value' => $redirect ) );

		$display_post = '';
		if ( isset( $buddyform['groups']['display_post'] ) ) {
			$display_post = $buddyform['groups']['display_post'];
		}

		$form_setup[] = new Element_Select( "<b>" . __( 'Display Post', 'buddyforms' ) . "</b>", "buddyforms_options[groups][display_post]", array(
				'nothing',
				'create a new tab',
				'before group activity'
			)
			, array( 'value' => $display_post,
			         'shortDesc' => __( "If you want to add the post to the home tab, you need to copy the single-post.php from <br> 'includes/templates/buddyforms/groups/single-post.php' to your theme and rename it to front.php 'groups/single/front.php'.", 'buddyforms' ) . "</p>
                                    <p> " . __( "If you want to change the new tab template, copy single-post.php to your theme 'buddyforms/groups/single-post.php'", 'buddyforms' ) . "</p>") );

		$display_content = '';
		if ( isset( $buddyform['groups']['display_content'] ) ) {
			$display_content = $buddyform['groups']['display_content'];
		}

		$form_setup[] = new Element_Checkbox( "<b>" . __( 'View', 'buddyforms' ) . "</b>", "buddyforms_options[groups][display_content]", array( "title"   => __( 'Display the Title', 'buddyforms' ),
		                                                                                                                                         "content" => __( 'Display the Content', 'buddyforms' ),
		                                                                                                                                         'meta'    => __( 'Display Post Meta', 'buddyforms' )
		), array( 'value' => $display_content ) );

	} else {
		$form_setup[] = new Element_HTML('<p>' . __('You need to activate the BuddyPress Groups Component for the plugin to work', 'buddyforms') . '</p>');
	}
	buddyforms_display_field_group_table( $form_setup, $field_id = 'global' );
}

add_filter( 'add_meta_boxes', 'buddyforms_apwg_admin_settings_metabox', 10, 2 );

function buddyforms_apwg_form_element_add_field( $form_fields, $form_slug, $field_type, $field_id ) {
	global $buddyforms, $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$buddyform = get_post_meta( get_the_ID(), '_buddyforms_options', true );

	if ( $field_type != 'apwg_taxonomy' ) {
		return $form_fields;
	}

	$apwg_taxonomy = Array();

	$value = '';
	if ( isset( $buddyform['form_fields'][ $field_id ]['apwg_taxonomy'] ) ) {
		$value = $buddyform['form_fields'][ $field_id ]['apwg_taxonomy'];
	}

	foreach ( $buddyforms as $key => $bform ) {

		if ( isset( $bform['slug'] ) && $form_slug == $bform['slug'] ) {
			continue;
		}

		$apwg_taxonomy['none'] = 'none';
		if ( isset( $bform['groups']['attache'] ) ) {
			$apwg_taxonomy[ $bform['slug'] ] = $bform['name'];
		}

	}

	$form_fields['general']['apwg_taxonomy'] = new Element_Select( '<b>' . __( "Attach Group Type:", 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][apwg_taxonomy]", $apwg_taxonomy, array( 'value' => $value ) );

	$multiple = 'false';
	if ( isset( $buddyform_options['form_fields'][ $field_id ]['multiple'] ) ) {
		$multiple = $buddyform_options['form_fields'][ $field_id ]['multiple'];
	}

	$form_fields['general']['multiple'] = new Element_Checkbox( '', "buddyforms_options[form_fields][" . $field_id . "][multiple]", array( 'multiple' => '<b>' . __( "Multiple:", 'buddyforms' ) . '</b>' ), array( 'value' => $multiple ) );

	return $form_fields;
}

add_filter( 'buddyforms_form_element_add_field', 'buddyforms_apwg_form_element_add_field', 1, 5 );


function buddyforms_apwg_frontend_form_element( $form, $form_args ) {
	global $buddyforms;


//    if ( !is_admin() || defined( 'DOING_AJAX' ) )
//        return;

	extract($form_args);


	if ( $customfield['type'] == 'apwg_taxonomy' ) {

		if ( $form_slug == $customfield['apwg_taxonomy'] || $customfield['apwg_taxonomy'] == 'none' ) {
			return;
		}

		$attached_tax_name = 'bf_apwg_' . $customfield['slug'];
		$term_list         = wp_get_post_terms( $post_id, $attached_tax_name, array( "fields" => "ids" ) );

		$multiple = '';
		if ( isset( $customfield['multiple'] ) ) {
			$multiple = $customfield['multiple'];
		}

		$args = array(
			'multiple'         => $multiple,
			'selected_cats'    => $term_list,
			'hide_empty'       => 0,
			//'id' => $key,
			'child_of'         => 0,
			'echo'             => false,
			'selected'         => false,
			'hierarchical'     => 1,
			'name'             => sanitize_title( $customfield['slug'] ),
			'class'            => 'postform bf-select2',
			'depth'            => 0,
			'tab_index'        => 0,
			'taxonomy'         => $attached_tax_name,
			'hide_if_empty'    => false,
			'show_option_none' => 'Nothing Selected'
		);

		$dropdown = wp_dropdown_categories( $args );

		if ( isset( $multiple ) && is_array( $multiple ) ) {
			$dropdown = str_replace( 'id=', 'multiple="multiple" id=', $dropdown );
		}

		if ( is_array( $term_list ) ) {
			foreach ( $term_list as $value ) {
				$dropdown = str_replace( ' value="' . $value . '"', ' value="' . $value . '" selected="selected"', $dropdown );
			}
		}
		$element = new Element_HTML( '<label>' . $customfield['name'] . ':</label><p><i>' . $customfield['description'] . '</i></p>' );
		$form->addElement( $element );

		$element = new Element_HTML( $dropdown );
		$form->addElement( $element );

	}

	return $form;

}

add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_apwg_frontend_form_element', 1, 2 );