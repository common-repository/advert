<?php

//modify toolbar
add_action('admin_bar_menu', 'advert_admin_modify_toolbar', 999);

//detect post status changes
add_action('transition_post_status', 'advert_send_post_status_emails', 10, 3);

//send emails
add_action('advert_send_emails', 'advert_emailer', 10, 4);

add_filter('override_post_lock', 'advert_control_lockout');
add_filter('check_advertiser', 'advert_check_advertiser', 10, 1);
add_filter('get_advertiser_id', 'advert_get_advertiser_id', 10, 1);

//redirect on new install
add_action('admin_init', 'advert_activate_redirect');

//display current Location rates
add_action('advert_current_rates', 'advert_display_current_rates');


// substitute for count_user_posts()
// apply_filters('check_advertiser', $userid)
function advert_check_advertiser($userid){
        
    $post_type = 'advert-advertiser';
    $count = 0;
    //$count = intval(count_user_posts($userid, $post_type));

    if($count === 0 || empty($count)){
	    global $wpdb;
	    $where = " WHERE post_type = '{$post_type}' AND post_author = $userid AND post_status = 'publish' ";
	    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
    }

    return $count;

}


function advert_get_advertiser_id($userid){
        
    $post_type = 'advert-advertiser';
    $id = '';
    $id = get_user_meta($userid, 'advert_advertiser_company_id'.get_current_blog_id(), true);

    if(empty($id)){
	    global $wpdb;
	    $where = " WHERE post_type = '{$post_type}' AND post_author = $userid ";
	    $id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts $where ORDER BY post_date DESC LIMIT 1" );

        update_user_meta($userid, 'advert_advertiser_company_id'.get_current_blog_id(), $id);
    }

    return $id;

}


function advert_display_current_rates(){
    
    ?>

        <div id="advert-current-banner-rates" style="display:none;">
        <table id="advert-current-rates" border="1" style="margin:0 auto;">
        <thead>
        <tr>
        <th style="width:200px;"><?php _e('Ad Location', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th style="width:200px;"><?php _e('AdCredit Amount', 'ADVERT_TEXTDOMAIN'); ?>*</th>
        </tr>
        </thead>

        <tbody style="text-align:center;">

        <?php
                
        global $wpdb;
        $args = " SELECT ID, post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'advert-location' ORDER BY post_title ASC ";
        $locations = $wpdb->get_results($args);

        foreach( $locations as $location ){

            echo '<tr>';
            echo '<td>'.$location->post_title.'</td>';
            echo '<td>'.get_post_meta( $location->ID, 'location_price', true ).'</td>';
            echo '</tr>';

        }

        ?>

        </tbody>
        </table>

        <p style="font-size:10px;max-width:400px;padding:5px;margin:0 auto;">
        <?php _e('* The AdCredit amount can either be per click or per 1,000 impressions depending on the Campaign pricing model you choose. ', 'ADVERT_TEXTDOMAIN');?>
        </p>

        </div>

    <?php

}


function advert_admin_modify_toolbar( $wp_admin_bar ) {

    //remove automatic ones
    $wp_admin_bar->remove_node( 'new-advert-banner' );
    $wp_admin_bar->remove_node( 'new-advert-campaign' );
    $wp_admin_bar->remove_node( 'new-advert-location' );

}


//display notices for non post types
function advert_notices(){

    global $notice_array;
    global $notice_num;

    if(is_admin()){

        foreach($notice_array as $notice){
            if($notice_num === 0){
                echo '<div id="message" class="error notice is-dismissible below-h2"><p>'.$notice.'</p></div>';
            }
            else{
                echo '<div id="message" class="updated notice is-dismissible below-h2"><p>'.$notice.'</p><button type="button" class="notice-dismiss advert-notice-dismiss"><span class="screen-reader-text">'. __( 'Dismiss this notice', 'ADVERT_TEXTDOMAIN' ) .'</span></button></div>';
            }
        }

    }

}



//Restrict access to media library
function advert_show_current_user_attachments( $query = array() ) {

    global $current_user;

        $args1 = array(
                'role'   => 'advert_manager',
                'fields' => 'ID'
        );
        $args2 = array(
                'role'   => 'advert_user',
                'fields' => 'ID'
        );

        $advertRole1 = get_users($args1);
        $advertRole2 = get_users($args2);
        $advertRoles = array_merge($advertRole1, $advertRole2);

        $user_id = get_current_user_id();

        if(in_array('advert_user', $current_user->roles)){
            $query['author'] = $user_id;
        }

        if(in_array('advert_manager', $current_user->roles)){
            $query['author__in'] = $advertRoles;
        }

        if(!in_array('advert_user', $current_user->roles) && !in_array('advert_manager', $current_user->roles) && !current_user_can('manage_options')){
            $query['author__not_in'] = $advertRoles;
        }

    return $query;

}


//specify query results: hides advert posts from main blog / hides posts from main blog for advert users 
function advert_query_set_only_author( $wp_query ) {

    if( is_admin() && is_user_logged_in() && current_user_can('edit_adverts') ){

        global $current_user;

        $args1 = array(
            'role'   => 'advert_manager',
            'fields' => 'ID'
        );
        $args2 = array(
            'role'   => 'advert_user',
            'fields' => 'ID'
        );

        $advertRole1 = get_users($args1);
        $advertRole2 = get_users($args2);
        $advertRoles = array_merge($advertRole1, $advertRole2);

        if( function_exists('get_current_screen') ){
            $currentScreen = get_current_screen();
        }

        if( in_array('advert_manager', $current_user->roles) && !empty($currentScreen) || in_array('advert_user', $current_user->roles) && !empty($currentScreen) ){

            if(in_array('advert_manager', $current_user->roles)){

                if ($currentScreen->id === 'upload'){
                    $wp_query->set( 'author__in', $advertRoles );
                }
       
            }//advert_manager

            if(in_array('advert_user', $current_user->roles)){

                $user_id = $current_user->ID;
                $company_id = get_user_meta( $user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);

                if($wp_query->query['post_type'] === 'advert-banner'){
                    $wp_query->set('meta_key', 'banner_owner');
		            $wp_query->set('meta_value', $company_id);
                }

                if($wp_query->query['post_type'] === 'advert-campaign'){
                    $wp_query->set('meta_key', 'campaign_owner');
		            $wp_query->set('meta_value', $company_id);
                }

                if ($currentScreen->id === 'upload'){
                    $wp_query->set( 'author', $current_user->ID );
                }

            }//advert_user

        }
 
        elseif(!in_array('advert_user', $current_user->roles) && !in_array('advert_manager', $current_user->roles) && !current_user_can('manage_options') && !current_user_can('subscriber')){
        $wp_query->set( 'author__not_in', $advertRoles );
        }

    }

return $wp_query;

}


function advert_wp_count_posts( $counts, $type, $perm ) {

    global $wpdb;

    if ( ! is_admin() || 'readable' !== $perm )
        return $counts;

    if(get_post_type() === 'advert-banner' || get_post_type() === 'advert-campaign'){

    $post_type_object = get_post_type_object($type);

    if ( current_user_can('publish_adverts') ) {
        return $counts;
    }

    $user_id = get_current_user_id();
    $company_id = get_user_meta( $user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
    $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_parent = $company_id GROUP BY post_status";
    $results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type, get_current_user_id() ), ARRAY_A );
    $counts = array_fill_keys( get_post_stati(), 0 );

    foreach ( $results as $row ) {
        $counts[ $row['post_status'] ] = $row['num_posts'];
    }
                
    return (object) $counts;

    }
        
    else{

    return $counts;    
        
    }

}


//upload to separate uploads folder: advert_uploads
//called on advert_admin() dependant on user role: advert user or advert manager
function advert_upload_dir( $param ){

    $param['path'] = $param['basedir'] . "/advert_uploads";
    $param['url']  = $param['baseurl'] . "/advert_uploads";
    return $param;

}



/**
*
* Override the takeover feature for regular advertisers
*
* Also applied to advert-post-metabox.php - removes the option to save post if locked
*
**/
function advert_control_lockout( $override ) {

    if( get_post_type() === 'advert-banner' || get_post_type() === 'advert-campaign' ){

        if( !current_user_can('publish_adverts') ){
            $override = FALSE;
        }

    }

    return $override;

}


/**
* send emails on post status change
*
* @since 1.0.0
*/  
function advert_send_post_status_emails($new_status, $old_status, $post){

    if( $post->post_type === 'advert-banner' || $post->post_type === 'advert-campaign' || $post->post_type === 'advert-advertiser'|| $post->post_type === 'advert-location' ){

        global $advert_options;

        $send_emails_pending = 0;
        if(array_key_exists('advert_pending_emails', $advert_options)){
            $send_emails_pending = intval($advert_options['advert_pending_emails']);
        }

        $send_emails_publish = 0;
        if(array_key_exists('advert_published_emails', $advert_options)){
            $send_emails_publish = intval($advert_options['advert_published_emails']);
        }

        if ( $new_status != $old_status ) {

            $to = get_the_author_meta('user_email', $post->post_author);

		    // Get the site domain and get rid of www.
		    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
		    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			    $sitename = substr( $sitename, 4 );
		    }

		    $from_email = 'advert@' . $sitename;

            $headers[] = 'From: NOREPLY <'.$from_email.'>';
 
            $headers[] = 'Bcc: ' . get_option('admin_email');
            //get the admin and all advert managers emails
            $args  = array( 'role' => 'advert_manager' );
            $users = get_users($args);

            foreach($users as $user){
                $headers[] = 'Bcc: ' . get_the_author_meta('user_email', $user->ID);
            }

            $post_type = str_replace('advert-', '', $post->post_type);

            if($new_status === 'pending' && $send_emails_pending === 1){
                $subject = sprintf( __('A new %s is pending review', 'ADVERT_TEXTDOMAIN'), $post_type );
                $message = sprintf( __('A new %1$s titled "%2$s" is pending review on %3$s.<br /><br /><br /><center><i>This is an automated message from AdVert</i></center>', 'ADVERT_TEXTDOMAIN'), $post_type, $post->post_title, get_option('blogname') );
                do_action('advert_send_emails',$to,$subject,$message,$headers);                
            }

            if($new_status === 'publish' && $send_emails_publish === 1){
                $subject = sprintf( __('A new %s has been published', 'ADVERT_TEXTDOMAIN'), $post_type );
                $message = sprintf( __('A new %1$s titled "%2$s" has been published on %3$s.<br /><br /><br /><center><i>This is an automated message from AdVert</i></center>', 'ADVERT_TEXTDOMAIN'), $post_type, $post->post_title, get_option('blogname') );
                do_action('advert_send_emails',$to,$subject,$message,$headers);
            }

        }
    }
    
}



function advert_emailer($to,$subject,$message,$headers){

    add_filter( 'wp_mail_content_type', 'advert_set_html_content_type' );
    wp_mail( $to, $subject, $message, $headers );
    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

}


//HTML formatted emails
function advert_set_html_content_type() {
	return 'text/html';
}


function advert_activate_redirect() {

    if( is_multisite() ){
        global $get_network_type;

        if( $get_network_type === false ){
            if ( get_option('advert_do_activation_redirect', false) ) {
                delete_option('advert_do_activation_redirect');
                wp_redirect( admin_url('admin.php?page=advert&welcome=1') );
            }            
        }

    }
    else{
        if ( get_option('advert_do_activation_redirect', false) ) {
            delete_option('advert_do_activation_redirect');
            wp_redirect( admin_url('admin.php?page=advert&welcome=1') );
        }
    }

}


/**
* Loads back end css, js
*
* @since 1.0.0
*/  
function advert_admin_scripts() {

    $version = new AdVert_For_Wordpress();
    $version = $version->get_advert_version();

    wp_register_script('advert', ADVERT_PLUGIN_URL . 'js/advertbe.js' ,array('jquery'), $version, true);
    wp_enqueue_script('advert');
    wp_enqueue_script("jquery");
    wp_enqueue_script('jquery-ui-datepicker'); 

    wp_enqueue_style('advert-style', ADVERT_PLUGIN_URL . 'css/advertbe.css');
    wp_enqueue_style('jquery-ui-datepicker-style',  ADVERT_PLUGIN_URL . 'css/jquery-ui-datepicker-style.css');

    if ( 'advert-banner' === get_post_type() || 'advert-advertiser' === get_post_type() || 'advert-location' === get_post_type() || 'advert-campaign' === get_post_type() )
        wp_dequeue_script( 'autosave' );

    if ( wp_is_mobile() )
        wp_enqueue_script( 'jquery-touch-punch' );

}