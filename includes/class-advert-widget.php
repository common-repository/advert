<?php
    
//adds a widget for ad placement
class AdVert_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'advert_widget', // Base ID
			__( 'AdVert Location', 'ADVERT_TEXTDOMAIN' ), // Name
			array( 'description' => __( 'Place an AdVert if you have locations created.', 'ADVERT_TEXTDOMAIN' ), ) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

        global $get_network_type;
        global $main_site_id;
        global $current_site_id;

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            switch_to_blog($main_site_id);
        }
        
	    if ( ! empty( $instance['locationID'] ) ) {

            $before_widget = $args['before_widget'];
            $after_widget  = $args['after_widget'];

            if(empty($before_widget)){        
                $before_widget = '<div id="'.$args['before_widget'].'" class="widget widget_advert_widget">';
            }

            if(empty($after_widget)){
                $after_widget = '</div>';
            }

            if ( !empty($instance['locationAlignment']) && $instance['locationAlignment'] == 'inline' ) {          
                $before_widget = str_replace('>', 'style="display:inline-block;vertical-align:middle;margin:5px;">', $before_widget);
            }

		    echo $before_widget;
                $advertshortcode = 'advert_location location_id="'.$instance['locationID'].'"';
                echo do_shortcode( '['.$advertshortcode.']' );
		    echo $after_widget;

        }   

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }
   
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            switch_to_blog($main_site_id);
        }

        $locationID = isset( $instance['locationID'] ) ? $instance['locationID'] : '';
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'ADVERT_TEXTDOMAIN' );
        $locationAlignment = isset( $instance['locationAlignment'] ) ? $instance['locationAlignment'] : '';

		?>
		<input hidden class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo get_the_title( $title ); ?>">
		<?php 

        $locations = get_posts(array('post_type' => 'advert-location' , 'posts_per_page' => -1, 'post_status' => 'publish'));
        echo '<p><label for="'.$this->get_field_id( 'locationID' ).'">';
        echo __( 'Select a Location:', 'ADVERT_TEXTDOMAIN' );
        echo '</label></p>';
        echo '<p><select id="'.$this->get_field_id( 'locationID' ).'" class="advert-location-widget widefat" name="'.$this->get_field_name( 'locationID' ).'" required>';
        echo '<option value="">'. __( 'None', 'ADVERT_TEXTDOMAIN' ) .'</option>';

        foreach($locations as $location){ ?>
        <option value="<?php echo esc_attr($location->ID); ?>" <?php selected($locationID, $location->ID);?> ><?php echo $location->post_title;?></option>
        <?php }

        echo '</select></p>';

        echo '<p><label for="'.$this->get_field_id( 'locationAlignment' ).'">';
        echo __( 'Set Alignment:', 'ADVERT_TEXTDOMAIN' );
        echo '</label></p>';

        echo '<p><select id="'.$this->get_field_id( 'locationAlignment' ).'" class="advert-location-widget widefat" name="'.$this->get_field_name( 'locationAlignment' ).'">';
        echo '<option value="">'. __( 'None', 'ADVERT_TEXTDOMAIN' ) .'</option>';
        if( $locationAlignment == 'inline' ){
            echo '<option value="inline" selected="selected">'. __( 'Inline', 'ADVERT_TEXTDOMAIN' ) .'</option>';
        }
        else{
            echo '<option value="inline">'. __( 'Inline', 'ADVERT_TEXTDOMAIN' ) .'</option>';            
        }
        echo '</select></p>';

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
        //$this->flush_widget_cache();
		$instance['locationID'] = ( ! empty( $new_instance['locationID'] ) ) ? $new_instance['locationID'] : '';
        $instance['locationAlignment'] = ( ! empty( $new_instance['locationAlignment'] ) ) ? $new_instance['locationAlignment'] : '';
        $instance['title'] = ( ! empty( $new_instance['locationID'] ) ) ? $new_instance['locationID'] : '';
		//$instance['locationID'] = $new_instance['locationID'];
        return $instance;
	}

} // End AdVert Widget Class

// register AdVert Widget widget
add_action('widgets_init', 'register_advert_widget');

function register_advert_widget() {

    register_widget( 'AdVert_Widget' );

}