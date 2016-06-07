<?php


/**
 * ShortCode/Template Tag
 *
 * Link to Attached Group
 *
 * @package Attach Posts to Groups Extension
 * @since 1.0.3
 */
add_shortcode( 'buddyforms_link_to_group', 'buddyforms_link_to_group' );
function buddyforms_link_to_group() {

	$post_id  = get_the_ID();
	$group_id = get_post_meta( $post_id, '_post_group_id', true );

	if ( ! isset( $group_id ) ) {
		return;
	}

	if ( ! function_exists( 'groups_get_group' ) ) {
		return;
	}

	$group = groups_get_group( array( 'group_id' => $group_id ) );

	return trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/' );

}

/**
 * ShortCode/Template Tag
 *
 * Link to Attached Post
 *
 * @package Attach Posts to Groups Extension
 * @since 1.0.3
 */
add_shortcode( 'buddyforms_link_to_post', 'buddyforms_link_to_post' );
function buddyforms_link_to_post() {

	if ( ! function_exists( 'bp_get_group_id' ) ) {
		return;
	}

	$group_id = bp_get_group_id();
	$post_id  = groups_get_groupmeta( $group_id, 'group_post_id' );

	if ( ! isset( $post_id ) ) {
		return;
	}

	return get_permalink( $post_id );

}
