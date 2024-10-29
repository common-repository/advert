<?php
/**
 * Catch Ajax request here
 */

add_action('wp_ajax_advert_view_log', 'advert_ads_view');
add_action('wp_ajax_nopriv_advert_view_log', 'advert_ads_view');

add_action('wp_ajax_advert_custom_log', 'advert_ads_custom_click');
add_action('wp_ajax_nopriv_advert_custom_log', 'advert_ads_custom_click');

add_action('wp_ajax_advert_feedback_log', 'advert_ads_feedback_click');
add_action('wp_ajax_nopriv_advert_feedback_log', 'advert_ads_feedback_click');

add_action('wp_ajax_advert_change_ads', 'advert_adchange');
add_action('wp_ajax_nopriv_advert_change_ads', 'advert_adchange');

add_action('wp_ajax_advert_welcome_message', 'update_advert_welcome_message');
add_action('wp_ajax_nopriv_advert_welcome_message', 'update_advert_welcome_message');

// When the ad is clicked type "C"
add_action('template_redirect', 'advert_ads_click', 1);

function advert_ads_click(){

    if(!isset($_GET['data']))
    return;

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        switch_to_blog($main_site_id);
    }

    $data = sanitize_text_field(decryptLink(urlencode($_GET['data'])));
    $nonce = sanitize_text_field($_GET['nonce']);
    $redir = sanitize_text_field($_GET['redir']);
    $valid = intval(advert_is_utf8($data));

    if($valid != 1){
        wp_safe_redirect( get_home_url() );
        exit();
    }

    if(wp_verify_nonce( $nonce, $data )){
        new ADVERT_Log( $data, 'c' );
    }

    //make sure the link hasnt been altered
    $link_check = explode('-|-',$data );
    $banner_link = get_post_meta($link_check[0], 'banner_link', true);

    if(!empty($redir) && $redir === $banner_link){

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }

        wp_redirect( $redir );
        exit();
    }
    else{

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }

        wp_safe_redirect( get_home_url() );
        exit(); 
    }

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        restore_current_blog();
    }

}

// When the ad is in view type "I"
function advert_ads_view(){

    $data = sanitize_text_field(decryptLink($_POST['data']));
    $valid = intval(advert_is_utf8($data));

    if($valid != 1)
    exit();

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        switch_to_blog($main_site_id);
    }

    if(wp_verify_nonce( $_POST['nonce'], $data ))
    new ADVERT_Log( $data, 'i' );

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        restore_current_blog();
    }

wp_die();

}

// When the iframe ad is clicked "C"
function advert_ads_custom_click(){

    $data = sanitize_text_field(decryptLink($_POST['data']));
    $valid = intval(advert_is_utf8($data));

    if($valid != 1)
    exit();

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        switch_to_blog($main_site_id);
    }

    if(wp_verify_nonce( $_POST['nonce'], $data ))
    new ADVERT_Log( $data, 'c' );

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        restore_current_blog();
    }

wp_die();

}

// Ad feedback
function advert_ads_feedback_click(){

    $feedback = sanitize_text_field(decryptLink($_POST['data']));
    $valid = intval(advert_is_utf8($feedback));

    if(wp_verify_nonce( $_POST['nonce'], $feedback ) && $valid === 1){

        global $get_network_type;
        global $main_site_id;
        global $current_site_id;

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            switch_to_blog($main_site_id);
        }

        $clicked = 1;

        $cookie_data_string = (isset($_COOKIE['avfeedback']) ? sanitize_text_field(decryptLink($_COOKIE['avfeedback'])) : '' );
        $valid = intval(advert_is_utf8($cookie_data_string));

        if($valid != 1){
            unset($_COOKIE['avfeedback']);

            if( $get_network_type === true && $current_site_id != $main_site_id ){
                restore_current_blog();
            }

            exit();
        }

        $cookie_data_array = (isset($_COOKIE['avfeedback']) && !empty($cookie_data_string) ? explode(",", $cookie_data_string) : [] );
        $data = explode('-|-', $feedback);
        $con = '12 HOUR';

        if( !in_array(intval($data[0]), $cookie_data_array) ){
            array_push($cookie_data_array, $data[0]);
            $clicked = intval(ADVERT_Log::get_click_by('f',intval($data[0]),$con));
        }

        if($clicked === 0){
            new ADVERT_Log( $feedback.'-|-'.$_POST['fbvalue'],'f' );
            $cookie_data = encryptLink(implode(",", $cookie_data_array));
            setcookie('avfeedback', $cookie_data, current_time('timestamp') + (86400 * 7), '/');
            echo 'feedback';
        }

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }

    }
    else{
        echo 'Error:' . __( 'An error has occurred', 'ADVERT_TEXTDOMAIN' );
    }

wp_die();

}


// this function runs if the advert-zone timestamp is 30 sec less than the actual current time
// caching plugins, and possibly a future rotator (though I dislike websites that rotate ads during the same session)
function advert_adchange(){

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        switch_to_blog($main_site_id);
    }

    $post = sanitize_text_field(decryptLink($_POST['data']));
    $howmany = intval(sanitize_text_field($_POST['count']));
    $data = explode('-|-', $post);

    if( is_array($data) && count($data) <= 5 ){
        $return = do_shortcode( '[advert_location location_id="'.$data[3].'" count_override="'.$howmany.'"]' );
        echo $return;
    }

    if( $get_network_type === true && $current_site_id != $main_site_id ){
        restore_current_blog();
    }

wp_die();

}



// update the welcome message
function update_advert_welcome_message(){

    if( wp_verify_nonce($_POST['advertwelcomepanelnonce'], 'advert-welcome-panel-nonce' )){
        update_user_meta( get_current_user_id(), 'show_advert_welcome_panel', empty( $_POST['visible'] ) ? 0 : 1 );
    }

wp_die();

}