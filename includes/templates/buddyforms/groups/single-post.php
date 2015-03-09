<?php

global $buddyforms;

$groups_post_id = groups_get_groupmeta( bp_get_current_group_id()   , 'group_post_id');
$group_type     = groups_get_groupmeta( bp_get_current_group_id()   , 'group_type' );
$form_slug   	= get_post_meta($groups_post_id, '_bf_form_slug', true);

$args = array(
    'post_id'       => $groups_post_id,
    'post_type'     => $group_type,
    'form_slug'     => $form_slug
);

$attached_post = new WP_Query( array('post_type' => $group_type, 'p' => $groups_post_id ) );
?>


<?php while ($attached_post->have_posts()) :$attached_post->the_post(); ?>

    <?php do_action('buddyforms_groups_single_title'        , get_the_title()   , $args) ?>
    <?php do_action('buddyforms_groups_single_content'      , get_the_content() , $args) ?>
    <?php do_action('buddyforms_groups_single_post_meta'    , $buddyforms['buddyforms'][$form_slug]['form_fields'], $args) ?>

<?php endwhile; ?>