<?php

function buddyforms_add_form_element_to_sidebar( $sidebar_elements ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	if ( ! defined( 'BP_VERSION' ) ) {
		return;
	}

	if ( bp_is_active( 'groups' ) ) {
		$sidebar_elements[] = new Element_HTML( '<p><a href="#" data-fieldtype="attachgrouptype" data-unique="unique" class="bf_add_element_action">Attach Group Type</a></p>' );
	}

	return $sidebar_elements;
}

add_filter( 'buddyforms_add_form_element_to_sidebar', 'buddyforms_add_form_element_to_sidebar', 1, 2 );

function buddyforms_agwp_admin_settings_sidebar_metabox() {
	add_meta_box( 'buddyforms_apwg', __( "BP Attach Post with Group", 'buddyforms' ), 'buddyforms_agwp_admin_settings_sidebar_metabox_html', 'buddyforms', 'normal', 'low' );
}

function buddyforms_agwp_admin_settings_sidebar_metabox_html( $form, $selected_form_slug ) {
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

	}
	buddyforms_display_field_group_table( $form_setup, $field_id = 'global' );
}

add_filter( 'add_meta_boxes', 'buddyforms_agwp_admin_settings_sidebar_metabox', 10, 2 );

function buddyforms_form_element_add_field_ge( $form_fields, $form_slug, $field_type, $field_id ) {
	global $buddyforms, $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$buddyform = get_post_meta( get_the_ID(), '_buddyforms_options', true );

	if ( $field_type != 'attachgrouptype' ) {
		return $form_fields;
	}

	$attachgrouptype = Array();

	$value = '';
	if ( isset( $buddyform['form_fields'][ $field_id ]['attachgrouptype'] ) ) {
		$value = $buddyform['form_fields'][ $field_id ]['attachgrouptype'];
	}

	foreach ( $buddyforms as $key => $bform ) {

		if ( isset( $bform['slug'] ) && $form_slug == $bform['slug'] ) {
			continue;
		}

		$attachgrouptype['none'] = 'none';
		if ( isset( $bform['groups']['attache'] ) ) {
			$attachgrouptype[ $bform['slug'] ] = $bform['name'];
		}

	}

	$form_fields['general']['attachgrouptype'] = new Element_Select( '<b>' . __( "Attach Group Type:", 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][attachgrouptype]", $attachgrouptype, array( 'value' => $value ) );

	$multiple = 'false';
	if ( isset( $buddyform_options['form_fields'][ $field_id ]['multiple'] ) ) {
		$multiple = $buddyform_options['form_fields'][ $field_id ]['multiple'];
	}

	$form_fields['general']['multiple'] = new Element_Checkbox( '', "buddyforms_options[form_fields][" . $field_id . "][multiple]", array( 'multiple' => '<b>' . __( "Multiple:", 'buddyforms' ) . '</b>' ), array( 'value' => $multiple ) );

	return $form_fields;
}

add_filter( 'buddyforms_form_element_add_field', 'buddyforms_form_element_add_field_ge', 1, 5 );


function buddyforms_attach_groups_create_edit_form_display_element_group( $form, $form_args ) {
	global $buddyforms;


//    if ( !is_admin() || defined( 'DOING_AJAX' ) )
//        return;

	extract($form_args);


	if ( $customfield['type'] == 'attachgrouptype' ) {

		if ( $form_slug == $customfield['attachgrouptype'] || $customfield['attachgrouptype'] == 'none' ) {
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

add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_attach_groups_create_edit_form_display_element_group', 1, 2 );
