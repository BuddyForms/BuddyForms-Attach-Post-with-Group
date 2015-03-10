<?php
/**
 * A widget to display BuddyForms List Admins & Moderators
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */

class BuddyForms_List_Moderators_Widget extends WP_Widget
{
    /**
     * Initialize the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function __construct() {
        $widget_ops = array(
            'classname'   => 'widget_buddyforms_list_moderators',
            'description' => __( 'BuddyForms List Admins & Moderators', 'buddyforms' )
        );

        parent::__construct( false, __( 'BuddyForms List Admins & Moderators', 'buddyforms' ), $widget_ops );
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


        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

        if( ! empty( $title ) )
            $title =  $before_title . $title . $after_title;

        echo $title;

        if ( bp_has_groups() ) :
            while ( bp_groups() ) : bp_the_group();
                if ( bp_group_is_visible() ) : ?>
                    <div id="item-list" class="widget">
                        <ul>
                            <li>
                            <?php

                            bp_group_list_admins();

                            if( bp_group_has_moderators() ) :
                                bp_group_list_mods();
                            endif;
                            ?>
                            </li>
                        </ul>
                    </div>
                    <div class="clear"></div>
                    <!-- End Widget Ansprechpartner -->
                <?php
                endif;
            endwhile;
        endif;

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