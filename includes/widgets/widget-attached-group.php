<?php
/**
 * A widget to display BuddyForms Attached Group
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */

class BuddyForms_Attached_Group_Widget extends WP_Widget
{
    /**
     * Initialize the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function __construct() {
        $widget_ops = array(
            'classname'   => 'widget_display_buddyforms_attached_group',
            'description' => __( 'BuddyForms Attached Group', 'buddyforms' )
        );

        parent::__construct( false, __( 'BuddyForms Attached Group', 'buddyforms' ), $widget_ops );
    }

    /**
     * Display the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function widget( $args, $instance ) {
        global $post, $buddyforms;

        extract( $args );

        if ( !bp_is_group() )
            return;

        $groups_post_id = groups_get_groupmeta( bp_get_group_id(), 'group_post_id' );

        if(empty($groups_post_id))
            return;

        $form_slug      = get_post_meta( $groups_post_id, '_bf_form_slug', true );

        if(empty($form_slug))
            return;

        foreach($buddyforms['buddyforms'][$form_slug]['form_fields'] as $key => $form_field){

            if($form_field['type'] == 'AttachGroupType')
                $Attach_group_post_type = $buddyforms['buddyforms'][$form_field['AttachGroupType']]['post_type'];

        }

        if(empty($Attach_group_post_type))
            return;

        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

        if( ! empty( $title ) )
            echo $before_title . $title . $after_title;


        $term = wp_get_post_terms($groups_post_id, $form_slug . '_attached_' . $Attach_group_post_type, array("fields" => "all"));

        if(is_wp_error($term))
            return;

        if ( isset($term[0]->name)) {

            $args=array(
                'name' => $term[0]->name,
                'post_type' => $Attach_group_post_type,
                'post_status' => 'publish',
                'posts_per_page' => 1
            );

            $get_the_post_thumbnail_attr = array(
                'class' => "avatar",
            );

            $app_posts = get_posts($args);

            $tmp = '';
            if( $app_posts ) {

                $tmp .= '<div id="item-list" class="widget">';
                $tmp .= '<ul>';

                foreach ( $app_posts as $post ) :

                    if ( $groups_post_id != $post->ID ) {

                        setup_postdata( $post );
                        $tmp .= '<a href="'.get_permalink().'" title="'.the_title_attribute(array('echo'=> 0)).'">';
                            $tmp .= '<li>';
                                $tmp .= get_the_post_thumbnail($post->ID , 'post-thumbnails' , $get_the_post_thumbnail_attr);
                                $tmp .= '<h3 class="post_title">'  . get_the_title()   . '</h3>';
                                $tmp .= '<p class="post_excerpt">' . get_the_excerpt() . '</p>';
                            $tmp .= '</li>';
                        $tmp .= '</a>';
                        $tmp .= '<div class="clear"></div>';
                    }

                endforeach;

                $tmp .= '</ul>';
                $tmp .= '</div>';

                $tmp .= '<div class="clear"></div>';

                echo $tmp;

                // Reset $tmp and the Query
                $tmp = '';
                wp_reset_query();
            }
        }

    }

    /**
     * Update any widget options
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function update( $new_instance, $old_instance ) {
        $instance          = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    /**
     * Show the widget options form
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

        ?>
        <div>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                    <?php _e( 'Title:', 'buddyforms' ) ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title ?>" />
                </label>
            </p>
        </div>
    <?php
    }
}