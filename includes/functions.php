<?php

/**
 * Check the dependencies and display a admin notice if needed
 *
 * @package buddyforms
 * @since 1.6
 */
function buddyforms_apwg_requirements(){


	if ( !defined( 'BP_VERSION' ) ) {
		add_action( 'admin_notices', create_function( '', 'printf(\'<div id="message" class="error"><p><strong>\' . __(\'BuddyForms Attache Post with Group needs BuddyPress to be installed and the Groups Component enabled. <a href="%s">Download it now</a>!\', " buddypress" ) . \'</strong></p></div>\', admin_url("plugin-install.php") );' ) );
		return;
	}

	if( ! defined( 'BUDDYFORMS_VERSION' )){
		add_action( 'admin_notices', create_function( '', 'printf(\'<div id="message" class="error"><p><strong>\' . __(\'BuddyForms Attache Post with Group needs BuddyForms to be installed. <a target="_blank" href="%s">--> Get it now</a>!\', " buddyforms" ) . \'</strong></p></div>\', "http://themekraft.com/store/wordpress-front-end-editor-and-form-builder-buddyforms/" );' ) );
		return;
	}

}
add_action('plugins_loaded', 'buddyforms_apwg_requirements');


/**
 * update post meta
 *
 * @package buddyforms
 * @since 1.0
 */
add_action( 'buddyforms_update_post_meta', 'bf_ge_updtae_post_meta', 99999, 2 );
function bf_ge_updtae_post_meta( $customfield, $post_id ) {
	global $buddyforms, $wp_taxonomies;

	$form_slug      = get_post_meta( $post_id, '_bf_form_slug', true );
	$post_group_id  = get_post_meta( $post_id, '_post_group_id', true );

	if ( ! isset( $form_slug ) ) {
		return;
	}

	// Set the post terms
	if ( $customfield['type'] == 'attachgrouptype' ) {

		$term_ids = '';

		if( isset( $_POST[ $customfield['slug'] ] ) ) {

			$value = $_POST[ $customfield['slug'] ];

			if( is_array( $value ) ){
				foreach( $value as $key => $term_id){
					$term_ids .= $term_id. ',';
				}
			} else {
				$term_ids .= $value;
			}

			wp_set_post_terms( $post_id, $term_ids, 'bf_apwg_' . $customfield['slug'], false );

		}

	}

}

/**
 * Delete a Group
 *
 * @package buddyforms
 * @since 1.6
 */
function bf_apwg_generate_attached_tax($field_slug, $attached_post_type, $attached_form_slug, $attached_group_id = FALSE) {

	$args = array(
		'post_type'      => $attached_post_type,
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'meta_query' => array(
			array(
				'key'     => '_post_group_id',
			),
			array(
				'key' => '_bf_form_slug',
				'value'   => $attached_form_slug,
			),
		),
	);


	if( $attached_group_id ){
		$args['meta_query'] = array(
			array(
				'key'     => '_post_group_id',
				'value'   => $attached_group_id,
			),
			array(
				'key' => '_bf_form_slug',
				'value'   => $attached_form_slug,
			),
		);
	}

	$attached_posts = new WP_Query( $args );

	while ( $attached_posts->have_posts() ) : $attached_posts->the_post();
		wp_set_object_terms( get_the_ID(), get_the_title(), 'bf_apwg_' . $field_slug );
	endwhile;

}


/**
 * Delete a Group
 *
 * @package buddyforms
 * @since 0.1-beta
 */
add_action( 'buddyforms_delete_post', 'buddyforms_delete_a_group' );
function buddyforms_delete_a_group( $post_id ) {
	$BuddyForms_GroupControl = new BuddyForms_GroupControl;
	$BuddyForms_GroupControl->delete_a_group( $post_id );
}

/**
 * Delete a post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_delete_a_group_post( $group_id ) {

	if(isset($_GET['action']) && $_GET['action'] == 'trash'){
		return;
	}

	$groups_post_id = groups_get_groupmeta( $group_id, 'group_post_id' );
	wp_delete_post( $groups_post_id );
}

add_action( 'groups_before_delete_group', 'buddyforms_delete_a_group_post' );

/**
 * Update a product post
 *
 * @package buddyforms
 * @since 0.1-beta
 */
function buddyforms_group_header_fields_save( $group_id ) {
	$groups_post_id = groups_get_groupmeta( $group_id, 'group_post_id' );

	$my_post = array(
		'ID'           => $groups_post_id,
		'post_title'   => $_POST['group-name'],
		'post_content' => $_POST['group-desc']
	);

	// update the post
	$post_id = wp_update_post( $my_post );
}

add_action( 'groups_group_details_edited', 'buddyforms_group_header_fields_save' );


function buddyforms_groups_group_settings_edited( $group_id ) {
	$groups_post_id = groups_get_groupmeta( $group_id, 'group_post_id' );

	$group_status = $_POST['group-status'];

	$post_status = $group_status;

	if ( $group_status == 'hidden' ) {
		$post_status = 'draft';
	}

	if ( $group_status == 'public' ) {
		$post_status = 'publish';
	}

	$post_status = apply_filters( 'bf_attached_grouppost_post_status', $post_status, $group_status );

	$my_post = array(
		'ID'          => $groups_post_id,
		'post_status' => $post_status
	);

	// update the post
	$post_id = wp_update_post( $my_post );

}

add_action( 'groups_group_settings_edited', 'buddyforms_groups_group_settings_edited' );


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

function buddyforms_groups_load_template_filter( $found_template, $templates ) {
	global $bp;

	if ( $bp->current_component == BP_GROUPS_SLUG && $bp->current_action == 'home' ) {
		$templates = buddyforms_ge_locate_template( 'buddyforms/groups/groups-home.php' );
		exit;
	}

	return apply_filters( 'buddyforms_groups_load_template_filter', $found_template, $templates );
}

/**
 * Locate a template
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */
function buddyforms_ge_locate_template( $file ) {
	if ( locate_template( array( $file ), false ) ) {
		locate_template( array( $file ), true );
	} else {
		include( BUDDYFORMS_GE_TEMPLATE_PATH . $file );
	}
}

add_filter( 'buddyforms_form_before_render', 'attached_group_buddyforms_form_before_render', 10, 2 );
function attached_group_buddyforms_form_before_render( $form, $args ) {
	if ( ! bp_is_group() ) {
		return $form;
	}

	extract( $args );
	ob_start();
	?>
	<script>
		jQuery(document).ready(function (jQuery) {
			jQuery(<?php echo "'#_bp_group_edit_nonce_" . $form_slug . "'" ?>).appendTo(jQuery('.form-actions'));
			jQuery('#group-id').appendTo(jQuery('.form-actions'));
		});
	</script>
	<?php
	$groups_js = ob_get_contents();
	ob_end_clean();

	$form->addElement( new Element_HTML( $groups_js ) );

	return $form;
}


add_action( 'buddyforms_groups_single_title', 'buddyforms_groups_single_title', 10, 2 );
function buddyforms_groups_single_title( $title, $args ) {
	global $buddyforms;

	if ( ! bp_is_group() ) {
		return;
	}

	extract( $args );

	if ( ! ( isset( $buddyforms[ $form_slug ]['groups']['display_content'] ) && in_array( 'title', $buddyforms[ $form_slug ]['groups']['display_content'] ) ) ) {
		return;
	}
	?>

	<?php do_action( 'buddyforms_before_groups_single_title' ) ?>

	<div class="entry-title">
		<?php echo $title ?>
	</div>

	<?php
}

add_action( 'buddyforms_groups_single_content', 'buddyforms_groups_single_content', 10, 2 );
function buddyforms_groups_single_content( $content, $args ) {
	global $buddyforms;

	if ( ! bp_is_group() ) {
		return;
	}

	extract( $args );

	if ( ! ( isset( $buddyforms[ $form_slug ]['groups']['display_content'] ) && in_array( 'content', $buddyforms[ $form_slug ]['groups']['display_content'] ) ) ) {
		return;
	}
	?>

	<?php do_action( 'buddyforms_before_groups_single_content' ) ?>

	<div class="entry-single-content">
		<?php echo $content ?>
	</div>

	<?php do_action( 'buddyforms_after_groups_single_content' ) ?>

	<?php
}

add_action( 'buddyforms_groups_single_post_meta', 'buddyforms_groups_single_post_meta', 10, 2 );
function buddyforms_groups_single_post_meta( $form_fields, $args ) {
	global $buddyforms;

	if ( ! bp_is_group() ) {
		return;
	}

	extract( $args );

	if ( bp_current_action() != $form_slug ) {
		;
	}

	return;

	if ( ! ( isset( $buddyforms[ $form_slug ]['groups']['display_content'] ) && in_array( 'meta', $buddyforms[ $form_slug ]['groups']['display_content'] ) ) ) {
		return;
	}

	?>
	<div class="entry-single-meta">

		<?php

		foreach ( $form_fields as $key => $customfield ) {

			if ( empty( $customfield['slug'] ) || $customfield['slug'] == 'buddyforms_form_title' || $customfield['slug'] == 'buddyforms_form_content' ) {
				continue;
			}

			$customfield_value = get_post_meta( get_the_ID(), $customfield['slug'], true );

			if ( ! empty( $customfield_value ) ) {
				$post_meta_tmp = '<div class="post_meta ' . $customfield['slug'] . '">';
				$post_meta_tmp .= '<label>' . $customfield['name'] . '</label>';

				if ( is_array( $customfield_value ) ) {
					$meta_tmp = "<p>" . implode( ',', $customfield_value ) . "</p>";
				} else {
					$meta_tmp = "<p>" . $customfield_value . "</p>";
				}

				switch ( $customfield['type'] ) {
					case 'taxonomy':
						$meta_tmp = get_the_term_list( $post->ID, $customfield['taxonomy'], "<p>", ' - ', "</p>" );
						break;
					case 'link':
						$meta_tmp = "<p><a href='" . $customfield_value . "' " . $customfield['name'] . ">" . $customfield_value . " </a></p>";
						break;
					default:
						apply_filters( 'buddyforms_form_element_display_frontend', $customfield );
						break;
				}

				$post_meta_tmp .= $meta_tmp;
				$post_meta_tmp .= '</div>';

				echo apply_filters( 'buddyforms_group_single_post_meta_tmp', $post_meta_tmp );


			}
		}
		?>

	</div>

	<?php
}
