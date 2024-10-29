<?php

function banner_start($post){

    add_meta_box('advert_banner', __( 'Banner Owner', 'ADVERT_TEXTDOMAIN' ), 'banner_meta_box', 'advert-banner' , 'normal' , 'high' );
    add_meta_box('advert_banner2', __( 'Banner Options', 'ADVERT_TEXTDOMAIN' ), 'banner_meta_box2', 'advert-banner' , 'normal' , 'high' );
    
    if(current_user_can('publish_adverts')){
        add_meta_box('advert_change_banner_ownership', __( 'Change Control', 'ADVERT_TEXTDOMAIN' ), 'banner_meta_box3', 'advert-banner' , 'side' , 'low' );
    }

    remove_meta_box( 'postimagediv', 'advert-banner', 'side' );
    remove_meta_box( 'slugdiv', 'advert-banner', 'normal' );
    remove_meta_box( 'submitdiv', 'advert-banner', 'side' );

    add_meta_box('submitdiv', __( 'Publishing Tools', 'ADVERT_TEXTDOMAIN' ), 'advert_post_submit_meta_box', 'advert-banner', 'side', 'high');

    //$post_id = get_the_ID();
    $banner_status = get_post_status($post->ID);
    $banner_owner  = get_post_meta($post->ID, 'banner_owner', true);

    //check if image, video or text are allowable fields
    $banner_location  = get_post_meta($post->ID, 'banner_location', true);
    $location_imagead  = intval(get_post_meta($banner_location, 'location_imagead', true));
    $location_videoad  = intval(get_post_meta($banner_location, 'location_videoad', true));
    $location_textad   = intval(get_post_meta($banner_location, 'location_textad', true));

    if ($banner_status === 'publish' && !current_user_can('publish_adverts') || empty($banner_owner))
        return;

    if( $location_imagead === 1){
        add_meta_box('postimagediv', __( 'Banner Image', 'ADVERT_TEXTDOMAIN' ), 'post_image_meta_box', 'advert-banner', 'normal', 'low');
    }

    if( $location_videoad === 1){
        add_meta_box('postvideodiv', __( 'Banner Video', 'ADVERT_TEXTDOMAIN' ), 'post_video_meta_box', 'advert-banner', 'normal', 'low');
    }

    if( $location_textad === 1){
        add_meta_box('posttextdiv', __( 'Banner Text', 'ADVERT_TEXTDOMAIN' ), 'post_text_meta_box', 'advert-banner', 'normal', 'low');
    }

    if ( !current_user_can('publish_adverts') ){
        remove_meta_box( 'advert_categorydiv', 'advert-banner', 'normal' );
    }

}


function post_image_meta_box($post){

    $thumbnail_id     = get_post_meta($post->ID, '_thumbnail_id', true);
    $banner_location = get_post_meta($post->ID , 'banner_location' ,true);
    $location_enforce = get_post_meta($banner_location, 'location_enforce', true);
    $location_width   = get_post_meta($banner_location, 'location_width', true);
    $location_height  = get_post_meta($banner_location, 'location_height', true);

    echo _wp_post_thumbnail_html( $thumbnail_id );
    echo '<noscript>'. __( 'Enable JavaScript to add/remove image', 'ADVERT_TEXTDOMAIN' ) .'</noscript>';

    if($location_enforce){
        echo '<p class="hide-if-no-js"><span class="advert-sm-info">'. __( 'Image dimensions should be:', 'ADVERT_TEXTDOMAIN' ) .'</span><span class="advert-sm-info banner-image-dimensions">'. __( 'Width:', 'ADVERT_TEXTDOMAIN' ) . $location_width .'px | '.  __( 'Height:', 'ADVERT_TEXTDOMAIN' ) . $location_height .'px</span></p>';
    }

    echo '<hr><p class="advert-tip"><span class="advert-sm-info">'. __( 'AdVert Tip: This is an image preview.', 'ADVERT_TEXTDOMAIN' ) .'</span></p>';
    
}


function post_video_meta_box($post){

    $banner_video_title = get_post_meta($post->ID, 'banner_video_title', true);
    $banner_video_url   = get_post_meta($post->ID, 'banner_video_url', true);
    $banner_location    = get_post_meta($post->ID, 'banner_location', true);
    $location_duration   = get_post_meta($banner_location, 'location_duration', true);

    if($banner_video_url){
        echo '<video class="advert-video-preview" width="100%" controls><source src="'.$banner_video_url.'" type="video/mp4" /></video>';
    }
    ?>

    <p class="advert-video-title"><?php echo $banner_video_title; ?></p>

    <p>
    <input type="hidden" id="advert_video_url" name="advert_video_url" class="media-input" />
    <input type="hidden" id="advert_video_id" name="advert_video_id" class="media-input" />
    <input type="hidden" id="advert_video_title" name="advert_video_title" class="media-input" />

    <?php
            
    if($banner_video_title){
        echo '<noscript>'. __( 'Enable JavaScript to add/remove video', 'ADVERT_TEXTDOMAIN' ) .'</noscript>';
        echo '<a href="#" class="advert-media-video-remove-button hide-if-no-js">'. __( 'Remove featured video', 'ADVERT_TEXTDOMAIN' ) .'</a>';
        echo '<a href="#" class="advert-media-video-button hide-if-no-js" style="display:none;">'. __( 'Set featured video', 'ADVERT_TEXTDOMAIN' ) .'</a>';
        if($location_duration){echo '<p class="hide-if-no-js"><span class="advert-sm-info">'. __( 'Video length should not exceed:', 'ADVERT_TEXTDOMAIN' ) .'</span><span class="advert-sm-info banner-video-length">'.$location_duration.' seconds</span></p>';}
        echo '<hr><p class="advert-tip"><span class="advert-sm-info">'. __( 'AdVert Tip: Select an Banner Image (if available) in case the video does not display or if you want a video thumbnail', 'ADVERT_TEXTDOMAIN' ) .'</span></p>';
    }
    else{
        echo '<noscript>'. __( 'Enable JavaScript to add/remove video', 'ADVERT_TEXTDOMAIN' ) .'</noscript>';
        echo '<a href="#" class="advert-media-video-remove-button hide-if-no-js" style="display:none;">'. __( 'Remove featured video', 'ADVERT_TEXTDOMAIN' ) .'</a>';
        echo '<a href="#" class="advert-media-video-button hide-if-no-js">'. __( 'Set featured video', 'ADVERT_TEXTDOMAIN' ) .'</a>';
        if($location_duration){echo '<p class="hide-if-no-js"><span class="advert-sm-info">Video length should not exceed:</span><span class="advert-sm-info banner-video-length">'.$location_duration.' seconds</span></p>';}
        echo '<hr><p class="advert-tip"><span class="advert-sm-info">'. __( 'AdVert Tip: Select an Banner Image in case the video does not display or if you want a video thumbnail', 'ADVERT_TEXTDOMAIN' ) .'</span></p>';
    }

    ?>

    </p>

    <?php
    
}


function post_text_meta_box($post){

    $banner_text_ad1     = get_post_meta($post->ID , 'banner_text_ad1' ,true);
    $banner_text_ad2     = get_post_meta($post->ID , 'banner_text_ad2' ,true);
    $banner_location     = get_post_meta($post->ID , 'banner_location' ,true);
    $location_characters1 = get_post_meta($banner_location, 'location_characters1', true);
    $location_characters2 = get_post_meta($banner_location, 'location_characters2', true);

    echo '<noscript><p style="text-align:center;">'. __( 'Enable JavaScript to add/remove text', 'ADVERT_TEXTDOMAIN' ) .'</p></noscript>';

    ?>

    <p class="hide-if-no-js">
    <label for="banner_text_ad1"><strong><?php _e('First block', 'ADVERT_TEXTDOMAIN');?></strong></label><br />
    <textarea maxlength="<?php if(!empty($location_characters1)){echo $location_characters1;}else{echo '800';} ?>" class="meta_banner_text" id="banner_text_ad1" name="banner_text_ad1" rows="3"><?php echo $banner_text_ad1; ?></textarea>
    </p>

    <p class="hide-if-no-js">
    <label for="banner_text_ad2"><strong><?php _e('Second block', 'ADVERT_TEXTDOMAIN');?></strong></label><br />
    <textarea maxlength="<?php if(!empty($location_characters2)){echo $location_characters2;}else{echo '800';} ?>" class="meta_banner_text" id="banner_text_ad2" name="banner_text_ad2" rows="3"><?php echo $banner_text_ad2; ?></textarea>
    </p>

    <hr>

    <p class="advert-tip"><span class="advert-sm-info"><?php _e('AdVert Tip: If this Banner has an image or video, the text ad will not display.', 'ADVERT_TEXTDOMAIN');?></span></p>

    <?php
    
}


function banner_meta_box($post){

    wp_nonce_field( 'banner_meta_box', 'banner_meta_box_nonce' );
    $advertisers      = get_posts(array('post_type' => 'advert-advertiser' , 'posts_per_page' => -1, 'post_status' => 'publish'));
    $banner_owner    = get_post_meta($post->ID , 'banner_owner' ,true);

    if( !current_user_can('publish_adverts') ){
        $user_id = get_current_user_id();
        $args = array('post_type' => 'advert-advertiser', 'author' => $user_id);
        $the_post = new WP_Query($args);
        $company = $the_post->posts[0]->ID;
        wp_reset_postdata();

        ?>

        <p>
        <select id="banner_owner" name="banner_owner" <?php if ( $banner_owner ){echo 'disabled';} ?> required>
        <option value="<?php echo $the_post->posts[0]->ID;?>" selected="selected"><?php echo $the_post->posts[0]->post_title;?></option>
        </select>
        </p>

        <?php
            
    }
    else{

        //quicklink to advertiser
        $url = esc_url(admin_url( 'post-new.php?post_type=advert-advertiser' ));

        ?>

        <p>
        <label for="banner_owner"><strong><?php _e('Attach this banner to an Advertiser', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
        <select id="banner_owner" name="banner_owner" <?php if ( $banner_owner ){echo 'disabled';} ?> required>
        <option value="" disabled><?php _e('Select Owner', 'ADVERT_TEXTDOMAIN'); ?></option>
        <?php foreach($advertisers as $advertiser){?>
        <option value="<?php echo $advertiser->ID;?>" <?php selected($banner_owner,$advertiser->ID);?> ><?php echo $advertiser->post_title;?></option>
        <?php } ?>
        </select>
        </p>

        <?php

        if( empty($banner_owner) ){
            echo '<p>'. __('Need another Advertiser, add more', 'ADVERT_TEXTDOMAIN') .'&nbsp;<a href="'.$url.'">'. __('here', 'ADVERT_TEXTDOMAIN') .'</a></p>';
        }

    }

}


function banner_meta_box2($post){

    $banner_owner    = get_post_meta($post->ID , 'banner_owner' ,true);
    $locations        = get_posts(array('post_type' => 'advert-location' , 'posts_per_page' => -1, 'post_status' => 'publish'));
    $banner_location = get_post_meta($post->ID , 'banner_location' ,true);
    $banner_priority = get_post_meta($post->ID , 'banner_priority' ,true);
    $banner_link     = get_post_meta($post->ID , 'banner_link' ,true);
    $banner_target   = get_post_meta($post->ID , 'banner_target' , true);

    if(current_user_can('publish_adverts')){
        $banner_custom_html = get_post_meta($post->ID , 'banner_custom_html' ,true);    
    }

    $location_duration  = get_post_meta($banner_location, 'location_duration', true);
    $banner_status     = get_post_status($post->ID);

    if (!$banner_owner && current_user_can('publish_adverts')){
        echo '<p class="banner-notice">'. __('Once you select the Banner owner (Advertiser) and either save draft or publish this banner, you will have additional options specific to the advertiser.', 'ADVERT_TEXTDOMAIN') .'</p>'; 
        return;
    }
    elseif(!$banner_owner && !current_user_can('publish_adverts')){
        echo '<p class="banner-notice">'. __('Once you enter a title for this Banner and save draft or submit for review, you will have additional options.', 'ADVERT_TEXTDOMAIN') .'</p>'; 
        return;
    }

    echo '<div id="advert-banner-translations" style="display:none;">';
    echo '<div class="advert-banner-translations1">'. __( 'Select Featured Video', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations2">'. __( 'Set featured video', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations5">'. __( 'Recommended video length should not exceed', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations6">'. __( 'seconds', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations7">'. __( 'Image dimensions should be', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations8">'. __( 'Width', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations9">'. __( 'Height', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '<div class="advert-banner-translations10">'. __( 'Manage Banner Categories', 'ADVERT_TEXTDOMAIN' ) .'</div>';
    echo '</div>';

    ?>

    <p>
    <label for="banner_location"><strong><?php _e('Banner Target Location', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
    <span class="banner_description"><?php _e('Where will this banner be displayed', 'ADVERT_TEXTDOMAIN'); ?></span><br />
    <?php $hasposts = get_posts('post_type=advert-location&post_status=publish'); if( !$hasposts && current_user_can('publish_adverts') ){$url = esc_url(admin_url( 'post-new.php?post_type=advert-location' )); echo '<p class="campaign-notice">'. __('No Locations have been created', 'ADVERT_TEXTDOMAIN') .'&nbsp;&ndash;&nbsp;<a href="'.$url.'">'. __('add new location', 'ADVERT_TEXTDOMAIN') .'</a></p>';}?>
    <select id="banner_location" name="banner_location" <?php if($banner_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> onchange="this.form.submit()" required>
    <option value="" disabled><?php _e('Select Location', 'ADVERT_TEXTDOMAIN'); ?></option>

    <?php 

    foreach($locations as $location){

        $location_enforce = intval(get_post_meta($location->ID, 'location_enforce', true));
        if($location_enforce === 1){
        $location_width   = get_post_meta($location->ID, 'location_width', true);
        $location_height  = get_post_meta($location->ID, 'location_height', true);
        $location_size    = $location_width.'-|-'.$location_height;
        }
        $location_duration    = get_post_meta($location->ID, 'location_duration', true);
        $location_characters1 = get_post_meta($location->ID, 'location_characters1', true);
        $location_characters2 = get_post_meta($location->ID, 'location_characters2', true);

        ?>

        <option value="<?php echo $location->ID;?>" <?php selected($banner_location,$location->ID);?> <?php if(!empty($location_size)){echo 'data-imagedimensions="'.$location_size.'"';} if(!empty($location_duration)){echo 'data-videolength="'.$location_duration.'"';} if(!empty($location_characters1) || !empty($location_characters2)){ echo 'data-textchar="'.$location_characters1.'-|-'.$location_characters2.'"';} ?> ><?php echo $location->post_title;?></option>
    
        <?php

    }
    
    ?>

    </select>
    </p>

    <p>
    <label for="banner_priority"><strong><?php _e('Priority', 'ADVERT_TEXTDOMAIN');?></strong></label><br />
    <span><?php _e('Greater numbers will display more often: 1 lowest | 10 Highest - Default is 5', 'ADVERT_TEXTDOMAIN');?></strong></span><br />
    <select name="banner_priority" id="banner_priority">
    <option value="10" <?php if($banner_priority === '10'){echo 'selected';} ?> >10</option>
    <option value="9" <?php if($banner_priority === '9'){echo 'selected';} ?> >9</option>
    <option value="8" <?php if($banner_priority === '8'){echo 'selected';} ?> >8</option>
    <option value="7" <?php if($banner_priority === '7'){echo 'selected';} ?> >7</option>
    <option value="6" <?php if($banner_priority === '6'){echo 'selected';} ?> >6</option>
    <option value="5" <?php if($banner_priority === '5'){echo 'selected';}else if($banner_priority === ''){echo 'selected';} ?> >5</option>
    <option value="4" <?php if($banner_priority === '4'){echo 'selected';} ?> >4</option>
    <option value="3" <?php if($banner_priority === '3'){echo 'selected';} ?> >3</option>
    <option value="2" <?php if($banner_priority === '2'){echo 'selected';} ?> >2</option>
    <option value="1" <?php if($banner_priority === '1'){echo 'selected';} ?> >1</option>
    </select>
    </p>

    <p>
    <label for="banner_link"><strong><?php _e('Link', 'ADVERT_TEXTDOMAIN'); ?></strong></strong></label><br />
    <span><?php _e('if applicable - ( format: http://www.example.com )', 'ADVERT_TEXTDOMAIN'); ?></span><br />
    <input class="meta_banner" id="banner_link" type="url" <?php if($banner_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="<?php echo $banner_link; ?>" name="banner_link"/>
    </p>

    <p>
    <label for="banner_target"><strong><?php _e('Target Window', 'ADVERT_TEXTDOMAIN'); ?></strong></strong></label><br />
    <span><?php _e('Specifies where to open the link', 'ADVERT_TEXTDOMAIN'); ?></strong></span><br />
    <select name="banner_target" id="banner_target">
    <option value="blank" <?php if($banner_target === 'blank'){echo 'selected';} ?> ><?php _e('Blank', 'ADVERT_TEXTDOMAIN'); ?></option>
    <option value="self" <?php if($banner_target === 'self'){echo 'selected';} ?> ><?php _e('Self', 'ADVERT_TEXTDOMAIN'); ?></option>
    <option value="parent" <?php if($banner_target === 'parent'){echo 'selected';} ?> ><?php _e('Parent', 'ADVERT_TEXTDOMAIN'); ?></option>
    <option value="top" <?php if($banner_target === 'top'){echo 'selected';} ?> ><?php _e('Top', 'ADVERT_TEXTDOMAIN'); ?></option>
    </select>
    </p>

    <?php 
    
    if(current_user_can('publish_adverts')){ 
        
        ?>

        <p>
        <label for="banner_custom_html"><strong><?php _e('Custom HTML', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
        <span class="aas_description"><?php _e('If you add custom HTML here, it will override the Banner (image, video or text) automatically.', 'ADVERT_TEXTDOMAIN'); ?></span><br />
        <textarea class="meta_banner" id="banner_custom_html" name="banner_custom_html" rows="7"><?php echo $banner_custom_html; ?></textarea>
        </p>

        <?php
    
    }

}


function banner_meta_box3($post){

    $users = get_users();

    ?>

    <p>
    <span class="aas_description"><?php _e('Useful when you manually create a new Banner and want to attach it to a user on the site.', 'ADVERT_TEXTDOMAIN');?></span><br /><br />
    <select id="banner_change_owner"  name="banner_change_owner">

    <?php 

    foreach($users as $user){

        if(user_can($user->ID, 'edit_adverts')){
            if(intval($post->post_author) === intval($user->ID)){
                echo '<option value="'.$user->ID.'" selected="selected">'.$user->user_login.'</option>';
            }
            else{
                echo '<option value="'.$user->ID.'">'.$user->user_login.'</option>';    
            }
        }

    } 
        
    ?>

    </select>
    </p>

    <p>
    Current Control: <?php if ( !user_can( $post->post_author, 'publish_adverts' )){echo _e('Advertiser', 'ADVERT_TEXTDOMAIN');}else{ _e('AdVert Manager', 'ADVERT_TEXTDOMAIN'); } ?>
    </p>

    <?php
        
}


//display error notices
function banner_admin_notice() {

if ( ! ( $errors = get_transient( 'banner_settings_errors' ) ) ) {
    return;
}

$message = '';
foreach ( $errors as $error ) {
    $message .= '<div class="error"><p>' . $error['message'] . '</p></div>';
}

echo $message;
    
delete_transient( 'banner_settings_errors' );
remove_action( 'admin_notices', 'banner_admin_notice' );

}


//control the messages
function banner_updated_messages( $messages ) {

    global $post, $post_ID;

    $messages['advert-banner'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Banner updated.', 'ADVERT_TEXTDOMAIN') ,
        6 => __('Banner published.', 'ADVERT_TEXTDOMAIN'),
        8 => __('Banner submitted.', 'ADVERT_TEXTDOMAIN'),
        9 => sprintf( __('banner scheduled for: <strong>%1$s</strong>.', 'ADVERT_TEXTDOMAIN'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
        10 =>  __('Banner draft updated.', 'ADVERT_TEXTDOMAIN')
    );

    return $messages;

}


function banner_save_meta($post_id) {

    if ('advert-banner' != get_post_type() || !current_user_can('edit_adverts'))
        return;

    if(!isset($_POST['banner_meta_box_nonce']) || !wp_verify_nonce($_POST['banner_meta_box_nonce'], 'banner_meta_box'))
        return;

    $banner_status = get_post_status($post_id);

    //justincase
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    //check if image, video or text are allowable fields
    $banner_location  = get_post_meta($post_id, 'banner_location', true);
    $location_videoad  = intval(get_post_meta($banner_location, 'location_videoad', true));
    $location_textad   = intval(get_post_meta($banner_location, 'location_textad', true));

    //double check the user
    $company_id = (isset($_POST['banner_owner']) ? intval($_POST['banner_owner']) : intval(get_post_meta($post_id, 'banner_owner', true)));

    if(!current_user_can('publish_adverts')){
        $user_id    = get_current_user_id();
        $company_id = intval(apply_filters('get_advertiser_id', $user_id));
    }

    if( $banner_status != 'publish' || current_user_can('publish_adverts') ){

        if(!empty($company_id)){

            global $wpdb;
            $wpdb->update($wpdb->posts , array('post_parent' => $company_id) , array('ID' => $post_id) , array('%d'),array('%d'));

            update_post_meta( $post_id, 'banner_owner' , sanitize_text_field(strip_tags($company_id)) );

        }

        if(isset($_POST['banner_location'])){
            update_post_meta( $post_id, 'banner_location' , sanitize_text_field(strip_tags($_POST['banner_location'])) );
        }

        if(isset($_POST['banner_link'])){
            update_post_meta( $post_id, 'banner_link' , esc_url($_POST['banner_link']) );
        }

    }

    if(isset($_POST['banner_priority'])){
        update_post_meta( $post_id, 'banner_priority' , number_format(strip_tags($_POST['banner_priority'])) );
    }

    if(isset($_POST['banner_target'])){
        update_post_meta( $post_id, 'banner_target' , sanitize_text_field(strip_tags($_POST['banner_target'])) );
    }

    if(isset($_POST['banner_custom_html']) && current_user_can('publish_adverts')){
        update_post_meta( $post_id, 'banner_custom_html' , htmlspecialchars($_POST['banner_custom_html']) );
    }

    if( $location_videoad === 1){

        if( isset($_POST['advert_video_url']) && $_POST['advert_video_url'] === 'advertremovevideo'){
            delete_post_meta( $post_id, 'banner_video_url');
            delete_post_meta( $post_id, 'banner_video_id');
            delete_post_meta( $post_id, 'banner_video_title');
        }
        else{
            if(isset($_POST['advert_video_url']) && !empty($_POST['advert_video_url']) ){
                update_post_meta( $post_id, 'banner_video_url' , esc_url($_POST['advert_video_url']) );
            }
            if(isset($_POST['advert_video_id']) && !empty($_POST['advert_video_id']) ){
                update_post_meta( $post_id, 'banner_video_id' , intval($_POST['advert_video_id']) );
            }
            if(isset($_POST['advert_video_title']) && !empty($_POST['advert_video_title'])){
                update_post_meta( $post_id, 'banner_video_title' , sanitize_text_field(strip_tags($_POST['advert_video_title'])) );
            }
        }

    }

    if( $location_textad === 1 ){

        if(isset($_POST['banner_text_ad1'])){
            update_post_meta( $post_id, 'banner_text_ad1' , sanitize_text_field(strip_tags($_POST['banner_text_ad1'])) );
        }
        if(isset($_POST['banner_text_ad2'])){
            update_post_meta( $post_id, 'banner_text_ad2' , sanitize_text_field(strip_tags($_POST['banner_text_ad2'])) );
        }

    }


    if (isset($_POST['banner_change_owner']) && current_user_can('publish_adverts') ){

        $post_author_id = get_post_field('post_author', $post_id);
        $newOwner = intval(strip_tags($_POST['banner_change_owner']));

        if ( $newOwner != $post_author_id && !wp_is_post_revision($post_id)){

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'banner_save_meta');

            $update_banner = array(
                'ID'            => $post_id,
                'post_author'   => $newOwner,
            );

            wp_update_post( $update_banner );

            // re-hook this function
            add_action('save_post', 'banner_save_meta');

        }

    }



    //check post status
    if($banner_status != 'publish')
    return;

    //update post to draft if checks fail
    $banner_location      = get_post_meta($post_id, 'banner_location', true);
    $location_enforce      = intval(get_post_meta($banner_location, 'location_enforce', true));
    $banner_image         = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
    $banner_video_url     = get_post_meta($post_id, 'banner_video_url', true);
    $location_textad       = get_post_meta($banner_location, 'location_textad', true);
    $location_characters1  = get_post_meta($banner_location, 'location_characters1', true);
    $location_characters2  = get_post_meta($banner_location, 'location_characters2', true);
    $banner_text_ad1      = get_post_meta($post_id, 'banner_text_ad1', true);
    $banner_text_ad2      = get_post_meta($post_id, 'banner_text_ad2', true);

    $banner_error = FALSE;

    if($banner_image[0] !=''){

        if($location_enforce === 1){

        $filename = explode('/',$banner_image[0]);
        $filename = end($filename);
        $filetype = wp_check_filetype($filename);
        $filetype = $filetype['ext'];

        if($filetype === 'jpg' || $filetype === 'jpeg' || $filetype === 'png' || $filetype === 'gif' || $filetype === 'ico' || $filetype === 'svg'){

            if($filetype === 'ico' || $filetype === 'svg'){
                add_settings_error('', '', __('Error: The image selected is not the correct format. Accepted formats are jpg, jpeg, png and gif. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), 'error');    
            }
            else{
                $location_width   = get_post_meta($banner_location, 'location_width', true);
                $location_height  = get_post_meta($banner_location, 'location_height', true);
                $img  = getimagesize($banner_image);
                $imgW = $img[0];
                $imgH = $img[1];

                if($imgW != $location_width && $imgH != $location_height){
                    $banner_error = TRUE;
                    add_settings_error('', '', sprintf( __('Error: The image selected is not the correct size. The width should be %1$spx and the height should be %2$spx. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), $location_width, $location_height), 'error');
                }
            }

        }

        }

    }//image check


    if($banner_video_url !=''){

        $filename = explode('/',$banner_video_url);
        $filename = end($filename);
        $filetype = wp_check_filetype($filename);
        $filetype = $filetype['ext'];

        if($filetype === 'mp4' || $filetype === 'mov' || $filetype === 'wmv' || $filetype === 'avi' || $filetype === 'mpg' || $filetype === 'ogv' || $filetype === '3gp' || $filetype === '3g2'){

            $banner_video_id = get_post_meta($post_id, 'banner_video_id', true);
            $video_meta = wp_get_attachment_metadata($banner_video_id);

            if($filetype != 'mp4'){
                $banner_error = TRUE;
                add_settings_error('', '', __('Error: The video selected is not mp4 format. AdVert currently uses mp4 format. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), 'error');    
            }
            else{
                $location_duration  = get_post_meta($banner_location, 'location_duration', true);
                if($location_duration != ''){
                    $videoLength = $video_meta['length'];

                    if($videoLength > $location_duration){
                        $banner_error = TRUE;
                        add_settings_error('', '', sprintf( __('Error: The video selected exceeds the recommended length of %1$s seconds. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), $location_duration), 'error');        
                    }  
                }
            }

        }

    }//video check


    if($location_textad === '1'){

        if($banner_text_ad1 !='' && strlen($banner_text_ad1) > $location_characters1 && $location_characters1 > 0 || strlen($banner_text_ad1) > 800){
            $banner_error = TRUE;
            add_settings_error('', '', __('Error: The text length for the first text block is too long. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), 'error');
        }

        if($banner_text_ad2 !='' && strlen($banner_text_ad2) > $location_characters2 && $location_characters2 > 0 || strlen($banner_text_ad2) > 800){
            $banner_error = TRUE;
            add_settings_error('', '', __('Error: The text length for the second text block is too long. The Banner has been saved as draft.', 'ADVERT_TEXTDOMAIN'), 'error');         
        }
    
    }//text check


    if($banner_error === TRUE){

        wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
        add_filter('redirect_post_location', 'banner_redirect_post_location_filter', 99);
        set_transient( 'banner_settings_errors', get_settings_errors(), 30 );

    }

}


/***
*
********** LOOK AT ME ***********
*
*/
function banner_redirect_post_location_filter($location) {

    remove_filter('redirect_post_location', __FUNCTION__, 99);
    //add crazy number to $message query so it doesnt display, oldschool texting 238378 = advert
    $location = add_query_arg('message', 238378, $location);
    return $location;

}


//add and change columns for banners
function custom_banner_columns($columns) {

    unset(
        $columns['title'],
        $columns['date'],
        $columns['taxonomy-advert_category']
    );

    if(current_user_can('publish_adverts')){
    $new_columns = array(
        'title'    => __('Title', 'ADVERT_TEXTDOMAIN'),
        'bid'      => __( 'BID', 'ADVERT_TEXTDOMAIN' ),
        'owner'    => __( 'Owner', 'ADVERT_TEXTDOMAIN' ),
        'location' => __( 'Location', 'ADVERT_TEXTDOMAIN' ),
        'priority' => __( 'Priority', 'ADVERT_TEXTDOMAIN' ),
        'link'     => __( 'Linked', 'ADVERT_TEXTDOMAIN' ),
        'custom'   => __( 'Custom HTML', 'ADVERT_TEXTDOMAIN' ),
        'banner'   => __( 'Banner Type', 'ADVERT_TEXTDOMAIN' ),
    );    
    }
    else{
    $new_columns = array(
        'title'    => __('Title', 'ADVERT_TEXTDOMAIN'),
        'bid'      => __( 'BID', 'ADVERT_TEXTDOMAIN' ),
        'location' => __( 'Location', 'ADVERT_TEXTDOMAIN' ),
        'priority' => __( 'Priority', 'ADVERT_TEXTDOMAIN' ),
        'link'     => __( 'Linked', 'ADVERT_TEXTDOMAIN' ),
        'banner'   => __( 'Banner Type', 'ADVERT_TEXTDOMAIN' ),
    );    
    }


    return array_merge($columns, $new_columns);

}



function custom_banner_column( $column, $post_id ) {

    switch ( $column ) {
        case 'title' :
        break;
        case 'bid' :
        echo $post_id; 
        break;
        case 'owner' :
        if(current_user_can('publish_adverts')){
        if (get_post_meta($post_id, 'banner_owner', true)){echo get_the_title(get_post_meta($post_id, 'banner_owner', true));}
        }
        break;
        case 'location' :
        if (get_post_meta($post_id, 'banner_location', true)){echo get_the_title( get_post_meta($post_id, 'banner_location', true ));}
        break;
        case 'priority' :
        $priority = ( is_numeric(get_post_meta($post_id, 'banner_priority', true)) ? number_format_i18n(get_post_meta($post_id, 'banner_priority', true)) : '' );
        echo $priority;
        break;
        case 'link' :
        if (get_post_meta($post_id, 'banner_link', true) !=''){ _e( 'Yes', 'ADVERT_TEXTDOMAIN' ); }else{ _e( 'No', 'ADVERT_TEXTDOMAIN' ); }
        break;
        case 'custom':
        if (get_post_meta($post_id, 'banner_custom_html', true) !=''){ _e( 'Yes', 'ADVERT_TEXTDOMAIN' ); }else{ _e( 'No', 'ADVERT_TEXTDOMAIN' ); }
        break;
        case 'banner':
        $thumb_id = get_post_thumbnail_id($post_id);
        $banner_video_title = get_post_meta($post_id, 'banner_video_title', true);
        $banner_text_ad1    = get_post_meta($post_id, 'banner_text_ad1', true);
        $banner_text_ad2    = get_post_meta($post_id, 'banner_text_ad2', true);
        if ($thumb_id && $banner_video_title === ''){
        _e( 'Image', 'ADVERT_TEXTDOMAIN' );
        }
        elseif($banner_video_title !=''){
        _e( 'Video', 'ADVERT_TEXTDOMAIN' );
        }
        elseif($banner_text_ad1 !='' || $banner_text_ad2 !=''){
        _e( 'Text', 'ADVERT_TEXTDOMAIN' );
        }
        else{
        _e( 'None', 'ADVERT_TEXTDOMAIN' );
        }
        break;
    }

}


function banner_sortable_columns( $sortable_columns ) {

    $sortable_columns['bid']      = 'bid';
    $sortable_columns['owner']    = 'owner';
    $sortable_columns['location'] = 'location';
    $sortable_columns['priority'] = 'priority';
    $sortable_columns['link']     = 'link';
    $sortable_columns['custom']   = 'custom';
    $sortable_columns['banner']   = 'banner';
    return $sortable_columns;

}