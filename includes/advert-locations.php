<?php

function location_start(){

    add_meta_box('advert_location', __( 'Location Info', 'ADVERT_TEXTDOMAIN' ), 'location_meta_box', 'advert-location' , 'normal' , 'high' );
    add_meta_box('advert_location2', __( 'Image Ads', 'ADVERT_TEXTDOMAIN' ), 'location_meta_box2', 'advert-location' , 'normal' , 'low' );
    add_meta_box('advert_location3', __( 'Video Ads', 'ADVERT_TEXTDOMAIN' ), 'location_meta_box3', 'advert-location' , 'normal' , 'low' );
    add_meta_box('advert_location4', __( 'Text Ads', 'ADVERT_TEXTDOMAIN' ), 'location_meta_box4', 'advert-location' , 'normal' , 'low' );

    remove_meta_box('postimagediv','advert-location','side');
    remove_meta_box( 'slugdiv', 'advert-location', 'normal' );
    remove_meta_box( 'submitdiv', 'advert-location', 'side' );

    add_meta_box('submitdiv', __( 'Publishing Tools', 'ADVERT_TEXTDOMAIN' ), 'advert_post_submit_meta_box', 'advert-location', 'side', 'high');

}


function location_meta_box($post){

    wp_nonce_field( 'location_meta_box', 'location_meta_box_nonce' );
    $location_rotation = get_post_meta($post->ID , 'location_rotation' , true);
    $location_price    = get_post_meta($post->ID , 'location_price' ,true);
    $location_enforce  = get_post_meta($post->ID , 'location_enforce' ,true);
    $location_width    = get_post_meta($post->ID , 'location_width' ,true);
    $location_height   = get_post_meta($post->ID , 'location_height' ,true);
    ?>

    <label ><strong><?php _e( 'Width and height of this location', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />

    <p>
    <span>Width</span><br />
    <input class="location_width" id="location_width" type="number" min="0" step="1" value="<?php echo $location_width; ?>" name="location_width"/>
    <span>&nbsp;px</span>
    </p>

    <p>
    <span>Height</span><br />
    <input class="location_height" id="location_height" type="number" min="0" step="1" value="<?php echo $location_height; ?>" name="location_height"/>
    <span>&nbsp;px</span>
    </p>

    <p>
    <label for="location_enforce">
    <input type="checkbox" id="location_enforce" name="location_enforce" value="1" <?php checked( $location_enforce, 1 ); ?> />
    <?php _e( 'Enforce width or height for this location.', 'ADVERT_TEXTDOMAIN' ); ?></label>
    <?php _e( '(For responsive, leave <strong>Enforce</strong> unchecked)', 'ADVERT_TEXTDOMAIN' ); ?>
    </p>

    <p>
    <label for="location_price"><strong><?php _e('Price of this location', 'ADVERT_TEXTDOMAIN');?></strong></label><br />
    <input class="location_price" id="advert_price" type="number" min="0" step="0.10" value="<?php echo $location_price; ?>" name="location_price" required />
    <span><?php _e( 'AdCredits', 'ADVERT_TEXTDOMAIN' ); ?></span>
    </p>

    <label for="location_rotation"><strong><?php _e( 'Location flow', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <span><?php _e( 'Selecting Random will discard the campaigns priority and select a campaign randomly', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <select name="location_rotation" id="location_rotation">
    <option value="priority" <?php if($location_rotation === 'priority'){echo 'selected';} ?> ><?php _e( 'Priority', 'ADVERT_TEXTDOMAIN' ); ?></option>
    <option value="random" <?php if($location_rotation === 'random'){echo 'selected';} ?> ><?php _e( 'Random', 'ADVERT_TEXTDOMAIN' ); ?></option>
    </select>

    <?php
        
}


function location_meta_box2($post){

    $location_imagead  = intval(get_post_meta($post->ID , 'location_imagead' ,true));

    ?>

    <p>
    <label for="location_imagead">
    <input type="checkbox" id="location_imagead" name="location_imagead" value="1" <?php checked( $location_imagead, 1 ); ?> />
    <?php _e( 'Allow Image Ads for this location', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </p>

    <?php
        
}


function location_meta_box3($post){

    $location_videoad   = intval(get_post_meta($post->ID , 'location_videoad' ,true));
    $location_duration  = get_post_meta($post->ID , 'location_duration' ,true);

    ?>

    <p>
    <label for="location_videoad">
    <input type="checkbox" id="location_videoad" name="location_videoad" value="1" <?php checked( $location_videoad, 1 ); ?> />
    <?php _e( 'Allow Video Ads for this location', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </p>

    <p>
    <span><?php _e( 'Video Duration', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <input class="location_duration" id="location_duration" type="number" step="1" value="<?php echo $location_duration; ?>" name="location_duration"/>
    <span><?php _e( 'maximum duration in seconds', 'ADVERT_TEXTDOMAIN' ); ?></span>
    </p>

    <?php
        
}


function location_meta_box4($post){

    $location_textad       = intval(get_post_meta($post->ID , 'location_textad' ,true));
    $location_characters1  = get_post_meta($post->ID , 'location_characters1' ,true);
    $location_characters2  = get_post_meta($post->ID , 'location_characters2' ,true);
    $location_textad_html1 = htmlspecialchars_decode(get_post_meta($post->ID , 'location_textad_html1' ,true));
    $location_textad_html2 = htmlspecialchars_decode(get_post_meta($post->ID , 'location_textad_html2' ,true));
    ?>

    <p>
    <label for="location_textad">
    <input type="checkbox" id="location_textad" name="location_textad" value="1" <?php checked( $location_textad, 1 ); ?> />
    <?php _e( 'Allow Text Ads for this location', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </p>

    <label><?php _e( 'Characters Available - Max 800 characters per line if left empty', 'ADVERT_TEXTDOMAIN' ); ?></label>
    <p>
    <span><?php _e( 'First block', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <input class="location_characters" id="location_characters1" type="number" step="1" value="<?php echo $location_characters1; ?>" name="location_characters1"/>
    </p>

    <p>
    <span><?php _e( 'Second block', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <input class="location_characters" id="location_characters2" type="number" step="1" value="<?php echo $location_characters2; ?>" name="location_characters2"/>
    </p>

    <p>
    <span class="aas_description"><?php _e( 'HTML to display before the text', 'ADVERT_TEXTDOMAIN' );?></span><br />
    <textarea class="meta_location_html" id="location_textad_html1" name="location_textad_html1" rows="5"><?php echo $location_textad_html1; ?></textarea>
    </p>

    <p>
    <span class="aas_description"><?php _e( 'HTML to display after the text', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <textarea class="meta_location_html" id="location_textad_html2" name="location_textad_html2" rows="5"><?php echo $location_textad_html2; ?></textarea>
    </p>

    <?php
        
}


//control the messages
function location_updated_messages( $messages ) {

    global $post, $post_ID;

    $messages['advert-location'] = array(
        0  => '', // Unused. Messages start at index 1.
        1  => __( 'Location updated.' , 'ADVERT_TEXTDOMAIN' ) ,
        6  => __( 'Location published.' , 'ADVERT_TEXTDOMAIN' ),
        8  => __( 'Location submitted.' , 'ADVERT_TEXTDOMAIN' ),
        9  => sprintf( __( 'Location scheduled for: <strong>%1$s</strong>.' , 'ADVERT_TEXTDOMAIN'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
        10 =>  __( 'Location draft updated.', 'ADVERT_TEXTDOMAIN' )
    );

    return $messages;

}


//save post meta data
function location_save_meta($post_id){

    if ('advert-location' != get_post_type() || !current_user_can('publish_adverts'))
        return;

    if(!isset($_POST['location_meta_box_nonce']) || !wp_verify_nonce($_POST['location_meta_box_nonce'], 'location_meta_box'))
        return;

    //justincase
    if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
        return;

    if(isset($_POST['location_width'])){
        update_post_meta( $post_id, 'location_width' , intval($_POST['location_width']) );
    }

    if(isset($_POST['location_height'])){
        update_post_meta( $post_id, 'location_height' , intval($_POST['location_height']) );
    }

    if(isset($_POST['location_enforce'])){
        update_post_meta( $post_id, 'location_enforce' , intval($_POST['location_enforce']) );
    }
    else{
        update_post_meta( $post_id, 'location_enforce' , 0 );    
    }

    if(isset($_POST['location_price']) && is_numeric($_POST['location_price']) ){
        update_post_meta( $post_id, 'location_price' , number_format($_POST['location_price'], 2) );
    }

    $location_rotation_array = array('random','priority');
    if(isset($_POST['location_rotation']) && in_array($_POST['location_rotation'], $location_rotation_array)){
        update_post_meta( $post_id, 'location_rotation' , $_POST['location_rotation'] );
    }

    if(isset($_POST['location_imagead'])){
        update_post_meta( $post_id, 'location_imagead' , intval($_POST['location_imagead']) );
    }
    else{
        update_post_meta( $post_id, 'location_imagead' , 0 );   
    }

    if(isset($_POST['location_videoad'])){
        update_post_meta( $post_id, 'location_videoad' , intval($_POST['location_videoad']) );
    }
    else{
        update_post_meta( $post_id, 'location_videoad' , 0 );    
    }

    if(is_numeric($_POST['location_duration']) || empty($_POST['location_duration'])){
        update_post_meta( $post_id, 'location_duration' , intval($_POST['location_duration']) );
    }

    if(isset($_POST['location_textad'])){
        update_post_meta( $post_id, 'location_textad' , intval($_POST['location_textad']) );
    }
    else{
        update_post_meta( $post_id, 'location_textad' , 0 );    
    }

    if($_POST['location_characters1'] || empty($_POST['location_characters1'])){
        update_post_meta( $post_id, 'location_characters1' , intval($_POST['location_characters1']) );
    }
    if($_POST['location_characters2'] || empty($_POST['location_characters2'])){
        update_post_meta( $post_id, 'location_characters2' , intval($_POST['location_characters2']) );
    }

    update_post_meta( $post_id, 'location_textad_html1' , wp_kses_post(htmlspecialchars($_POST['location_textad_html1'])) );
    update_post_meta( $post_id, 'location_textad_html2' , wp_kses_post(htmlspecialchars($_POST['location_textad_html2'])) );

}//save stuff



//add and change columns for advertisers
function custom_location_columns($columns) {

    unset(
        $columns['title'],
        $columns['date']
    );


    $new_columns = array(
        'title'     => __('Title', 'ADVERT_TEXTDOMAIN'),
        'lid'       => __('LID', 'ADVERT_TEXTDOMAIN'),
        'price'     => __( 'Price', 'ADVERT_TEXTDOMAIN' ),
        'rotation'  => __( 'Rotation', 'ADVERT_TEXTDOMAIN' ),
        'shortcode' => __( 'Shortcode', 'ADVERT_TEXTDOMAIN' ),
    );

    return array_merge($columns, $new_columns);

}



function custom_location_column( $column, $post_id ) {

    switch ( $column ) {
        case 'title' :
        break;
        case 'lid' :
        echo $post_id; 
        break;
        case 'price' :
        $location_value = get_post_meta( $post_id , 'location_price' , true );
        if(!empty($location_value)){echo number_format($location_value, 2);}
        break;
        case 'rotation' :
        if( get_post_meta( $post_id , 'location_rotation' , true ) === 'random' ){
        _e( 'Random', 'ADVERT_TEXTDOMAIN' );    
        }
        else{
        _e( 'Priority', 'ADVERT_TEXTDOMAIN' );
        }
        break;
        case 'shortcode':
        echo '[advert_location location_id="'.$post_id.'"]';
        break;
    }

}


function location_sortable_columns( $sortable_columns ) {

    $sortable_columns['lid']      = 'lid';
    $sortable_columns['rotation'] = 'rotation';
    $sortable_columns['price']    = 'price';
    return $sortable_columns;

}