<?php
/**
 * A widget to display BuddyForms All Posts of this Group
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */

class BuddyForms_All_Posts_of_this_Group_Widget extends WP_Widget
{
    /**
     * Initialize the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function __construct() {
        $widget_ops = array(
            'classname'   => 'widget_display_buddyforms_all_posts_of_group',
            'description' => __( 'BuddyForms APWG Taxonomy Posts', 'buddyforms' )
        );

        parent::__construct( false, __( 'BuddyForms APWG Taxonomy Posts', 'buddyforms' ), $widget_ops );
    }

    /**
     * Display the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function widget( $args, $instance ) {
        global $buddyforms, $post;

        extract( $args );

        if ( ! bp_is_group() ) {
            return;
        }

        $groups_post_id = groups_get_groupmeta( bp_get_group_id(), 'group_post_id' );

        if ( empty( $groups_post_id ) ) {
            return;
        }

        $group_type = groups_get_groupmeta( bp_get_group_id(), 'group_type' );

        if ( empty( $group_type ) ) {
            return;
        }

        $form_slug = get_post_meta( $groups_post_id, '_bf_form_slug', true );

        if ( empty( $form_slug ) ) {
            return;
        }


        $form_select                 = ! empty( $instance['form_select'] ) ? $instance['form_select'] : '';
        $title_attached_groups       = ! empty( $instance['title_attached_groups'] ) ? $instance['title_attached_groups'] : '';
        $title_other_attached_groups = ! empty( $instance['title_other_attached_groups'] ) ? $instance['title_other_attached_groups'] : '';
        $widget_class                = ! empty( $instance['widget_class'] ) ? $instance['widget_class'] : '';


        if ( ! isset( $buddyforms[ $form_select ]['form_fields'] ) ) {
            return;
        }

        foreach ( $buddyforms[ $form_select ]['form_fields'] as $key => $form_field ) {

            if ( $form_field['type'] == 'apwg_taxonomy' ) {
                $apwg_taxonomys[ $form_field['slug'] ] = $buddyforms[ $form_field['apwg_taxonomy'] ]['post_type'];
            }


        }

        if ( ! is_array( $apwg_taxonomys ) ) {
            return;
        }

        foreach ( $apwg_taxonomys as $field_slug => $post_type ) {

            $term = wp_get_post_terms( $groups_post_id, 'bf_apwg_' . $field_slug, array( "fields" => "all" ) );

            if ( is_wp_error( $term ) ) {
                return;
            }

            if ( isset( $term[0]->name ) ) {

                $args = array(
                    'post_type'              => $buddyforms[ $form_select ]['post_type'],
                    'bf_apwg_' . $field_slug => $term[0]->slug,
                    'order'                  => 'ASC',
                    array(
                        'key'     => '_bf_form_slug',
                        'value'   => $form_select,
                    ),

                );

                $gr_query = new WP_Query( $args );

                $tmp = '';
                if ( $gr_query->have_posts() ) {

                    $attached_post_type = $gr_query->query['post_type'];
                    $title = $group_type == $attached_post_type ? $title_attached_groups : $title_other_attached_groups;
                    echo $before_title . $title . $after_title;

                    $tmp .= '<div class="widget ' . $widget_class . '">';
                    $tmp .= '<ul>';

                    while ( $gr_query->have_posts() ) : $gr_query->the_post();

                        if ( ! get_post_meta( get_the_ID(), '_bf_form_slug', true ) ) {
                            continue;
                        }

                        if ( get_the_ID() != $groups_post_id && $term[0]->slug !=  $post->post_name ) {
                            $tmp .= '<a href="' . get_permalink() . '" title="' . the_title_attribute( Array( 'echo' => 0 ) ) . '" class="clickable_box">';
                            $tmp .= '<li>';
                            $tmp .= get_the_post_thumbnail( get_the_id(), array( 50, 50 ) );
                            $tmp .= '<h4>' . get_the_title() . '</h4>';
                            $tmp .= '<p class="post_excerpt">' . get_the_excerpt() . '</p>';
                            $tmp .= '</li>';
                            $tmp .= '</a>';
                            $tmp .= '<div class="clear"></div>';
                        }
                    endwhile;

                    $tmp .= '</ul></div>';

                    echo $tmp;

                    // Reset $tmp and the Query
                    $tmp = '';
                    wp_reset_query();
                }
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

        $instance['form_select'] = strip_tags( $new_instance['form_select'] );
        $instance['title_attached_groups'] = strip_tags( $new_instance['title_attached_groups'] );
        $instance['title_other_attached_groups'] = strip_tags( $new_instance['title_other_attached_groups'] );
        $instance['widget_class'] = strip_tags( $new_instance['widget_class'] );

        return $instance;
    }

    /**
     * Show the widget options form
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function form( $instance ) {
        global $buddyforms;


        $form_select_options = Array();
        foreach($buddyforms as $form_slug => $buddyform){

            if(isset($buddyform['form_fields'])){
                foreach($buddyform['form_fields'] as $field_key => $form_field){
                    if($form_field['type'] == 'apwg_taxonomy') {
                        $form_select_options[ $form_field['slug'] ]['name']      = $buddyform['name'];
                        $form_select_options[ $form_field['slug'] ]['form_slug'] = $form_slug;
                    }
                }
            }

        }
        $form_select  = ! empty( $instance['form_select'] ) ? esc_attr( $instance['form_select'] ) : '';
        $title_attached_groups = ! empty( $instance['title_attached_groups'] ) ? esc_attr( $instance['title_attached_groups'] ) : '';
        $title_other_attached_groups = ! empty( $instance['title_other_attached_groups'] ) ? esc_attr( $instance['title_other_attached_groups'] ) : '';
        $widget_class = ! empty( $instance['widget_class'] ) ? esc_attr( $instance['widget_class'] ) : '';

        ?>
        <div>
            <p>
                <label class="widefat" for="<?php echo $this->get_field_id( 'form_select' ); ?>">
                    <?php _e( 'Select the Form to use:', 'buddyforms' ) ?>
                    <select id="<?php echo $this->get_field_id( 'form_select' ); ?>" name="<?php echo $this->get_field_name( 'form_select' ); ?>">
                        <option value="none">Select a Form</option>
                        <?php
                        foreach($form_select_options as $field_slug => $form){
                            echo '<option ' . selected($form_select, $form['form_slug']) . ' value="' . $form['form_slug'] . '">' . $form['name'] . '</option>';
                        }
                        ?>
                    </select>
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'title_attached_groups' ); ?>">
                    <?php _e( 'Item Label e.g. Other Posts of the same apwg taxonomy', 'buddyforms' ) ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title_attached_groups' ); ?>" name="<?php echo $this->get_field_name( 'title_attached_groups' ); ?>" type="text" value="<?php echo $title_attached_groups ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'title_other_attached_groups' ); ?>">
                    <?php _e( 'Parent Label e.g. Group Posts' , 'buddyforms' ) ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title_other_attached_groups' ); ?>" name="<?php echo $this->get_field_name( 'title_other_attached_groups' ); ?>" type="text" value="<?php echo $title_other_attached_groups ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'widget_class' ); ?>">
                    <?php _e( 'Add Custom Class', 'buddyforms' ) ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'widget_class' ); ?>" name="<?php echo $this->get_field_name( 'widget_class' ); ?>" type="text" value="<?php echo $widget_class ?>" />
                </label>
            </p>
        </div>
    <?php
    }
}