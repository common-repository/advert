<?php

add_action('plugins_loaded', 'advert_admin_start');

function advert_admin_start() {
   
    if(is_user_logged_in()){


        if( is_multisite() ){

            global $advert_network_options;
            global $get_network_type;
            global $main_site_id;
            global $current_site_id;

            if( array_key_exists('advert_active_campaigns_none',$advert_network_options) ){
                $advert_active_campaigns_none = true;
            }
            else{
                $advert_active_campaigns_none = false;
            }

        }
        else{

            $get_network_type = false;
            $main_site_id     = get_current_blog_id();

        }


        if( $get_network_type === false || get_current_blog_id() == $main_site_id || $advert_active_campaigns_none ){

            global $current_user;
            global $advert_options;

            $post_page_switch = '';
            if( array_key_exists('advert_allow_editors_turn_off_ads',$advert_options) ){
                $post_page_switch = intval($advert_options['advert_allow_editors_turn_off_ads']);
            }

            require_once( ADVERT_PLUGIN_DIR . 'admin/advert-admin-functions.php' );      
            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-widget.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-ajax.php' );

            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-payments.php' );
            $paymentfilter = new AdVert_Payments;
            $paymentfilter->advert_create_payment_filters();

            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-post-types.php' );
                new AdVert_Post_Types();

            if( $post_page_switch === 1 ){
                require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-post-page-metaboxes.php' );
            }

            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-post-metabox.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-banners.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-advertisers.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-campaigns.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-locations.php' );

            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-screentabs.php' );
                new AdVert_Screentabs();

            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-analysis.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-analysis-drilldown.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-control-panel.php' );
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-archive.php' );

            /** AdVert filters and actions specific to users in the Admin area **/

            //enqueue admin scripts
            add_action('admin_enqueue_scripts', 'advert_admin_scripts');

            //filter the ajax for displaying media
            add_filter('ajax_query_attachments_args', 'advert_show_current_user_attachments', 10, 1);

            //limit the viewable items for AdVert users
            //hides any data specific to AdVert from the normal WordPress stuff (unless Admin posts stuff)
            add_action('pre_get_posts', 'advert_query_set_only_author');

            //change post count for AdVert user
            add_filter('wp_count_posts', 'advert_wp_count_posts', 10, 3);

            //post override filtering for advert users
            add_filter( 'override_post_lock', 'advert_control_lockout' );

            //upload to separate uploads folder: advert_uploads
            if(is_array($current_user)){
                $current_user->roles[0];

                if( in_array('advert_manager', $current_user->roles) || in_array('advert_user', $current_user->roles) ){
                    add_filter('upload_dir', 'advert_upload_dir');
                }
            }

        }
        else{

            //allow some admin functions
            require_once( ADVERT_PLUGIN_DIR . 'admin/advert-admin-functions.php' ); 
                        
            //enqueue admin css style
            add_action('admin_enqueue_scripts', 'advert_connected_multisite_css');

            //allow widgets
            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-widget.php' );

        }

        //add the top-level user page
        if ( !current_user_can( 'publish_adverts' ) && current_user_can('subscriber') || !current_user_can( 'publish_adverts' ) && current_user_can('edit_adverts') ){
                require_once( ADVERT_PLUGIN_DIR . 'includes/advert-dashboard-user.php' );
                add_action('admin_menu', 'register_advert_user_menu');

                add_action('admin_menu', 'register_advert_user_menu_transactions');
            }
            

        //add the top-level admin page
        if( current_user_can('publish_adverts')){
            require_once( ADVERT_PLUGIN_DIR . 'includes/advert-dashboard.php' );
            add_action('admin_menu', 'register_advert_menu');

            if( $get_network_type === false || get_current_blog_id() == $main_site_id || $advert_active_campaigns_none ){

                new AdVert_Control_Panel();

            }

            add_action('admin_menu', 'register_advert_menu_transactions');
        }

    }

}



function register_advert_menu(){

    if( is_multisite() ){

        global $advert_network_options;
        global $get_network_type;
        global $main_site_id;
        global $current_site_id;

        if( $get_network_type === true && $current_site_id != $main_site_id && !array_key_exists('advert_active_campaigns_none',$advert_network_options) ){

            return;

        }

    }

    global $wpdb;

    $query_pending_advertisers = " SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'pending' AND post_type = 'advert-advertiser' ";
    $query_pending_banners     = " SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'pending' AND post_type = 'advert-banner' ";
    $query_pending_campaigns   = " SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'pending' AND post_type = 'advert-campaign' ";
    $advertiser_post_count     = $wpdb->get_var($query_pending_advertisers);
    $banner_post_count        = $wpdb->get_var($query_pending_banners);
    $campaign_post_count       = $wpdb->get_var($query_pending_campaigns);
    $total_post_count          = $advertiser_post_count + $banner_post_count + $campaign_post_count;
    $advert_menu           = ( !empty($total_post_count) ? sprintf( __( 'AdVert %s', 'ADVERT_TEXTDOMAIN' ), "<span class='update-plugins count-$total_post_count'><span class='update-count'>" . number_format_i18n($total_post_count) . "</span></span>" ) : __( 'AdVert', 'ADVERT_TEXTDOMAIN' ) );      
    $advert_advertiser_menu    = ( !empty($advertiser_post_count) ? sprintf( __( 'Advertisers %s', 'ADVERT_TEXTDOMAIN' ), "<span class='update-plugins count-$advertiser_post_count'><span class='update-count'>" . number_format_i18n($advertiser_post_count) . "</span></span>" ) : __( 'Advertisers', 'ADVERT_TEXTDOMAIN' ) );
    $advert_banner_menu       = ( !empty($banner_post_count) ? sprintf( __( 'Banners %s', 'ADVERT_TEXTDOMAIN' ), "<span class='update-plugins count-$banner_post_count'><span class='update-count'>" . number_format_i18n($banner_post_count) . "</span></span>" ) : __( 'Banners', 'ADVERT_TEXTDOMAIN' ) );
    $advert_campaign_menu      = ( !empty($campaign_post_count) ? sprintf( __( 'Campaigns %s', 'ADVERT_TEXTDOMAIN' ), "<span class='update-plugins count-$campaign_post_count'><span class='update-count'>" . number_format_i18n($campaign_post_count) . "</span></span>" ) : __( 'Campaigns', 'ADVERT_TEXTDOMAIN' ) );

    //top level page
    add_menu_page( __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), $advert_menu, 'publish_adverts', 'advert', 'advert_dashboard_start', '' );

    //change name of top level name in the submenu area
    add_submenu_page( 'advert', __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ), __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert', 'advert_dashboard_start' );

    //add submenus
    add_submenu_page( 'advert', __( 'Advertisers', 'ADVERT_TEXTDOMAIN' ), $advert_advertiser_menu, 'publish_adverts', 'edit.php?post_type=advert-advertiser', '' );
    add_submenu_page( 'advert', __( 'Banners', 'ADVERT_TEXTDOMAIN' ), $advert_banner_menu, 'edit_adverts', 'edit.php?post_type=advert-banner', '' );
    add_submenu_page( 'advert', __( 'Campaigns', 'ADVERT_TEXTDOMAIN' ), $advert_campaign_menu, 'publish_adverts', 'edit.php?post_type=advert-campaign', '' );
    add_submenu_page( 'advert', __( 'Locations', 'ADVERT_TEXTDOMAIN' ), __( 'Locations', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'edit.php?post_type=advert-location', '' );
    add_submenu_page( 'advert', __( 'Analysis Overview', 'ADVERT_TEXTDOMAIN' ), __( 'Analysis', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-analysis-overview', 'advert_load_analysis' );
    add_submenu_page( 'advert', __( 'Analysis Drilldown', 'ADVERT_TEXTDOMAIN' ), __( 'Analysis', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-analysis-drilldown', 'advert_load_analysis_drilldown' );

}


function register_advert_user_menu(){

    global $advert_options;
    $new_user_reg = 0;
    $user_id = get_current_user_id();
    $howmany = apply_filters('check_advertiser', $user_id);

    if( array_key_exists('advert_register_users',$advert_options) ){
        $new_user_reg = intval($advert_options['advert_register_users']);
    }

    //check if the options allow new user registration
    if($new_user_reg === 1 || current_user_can('edit_adverts')){

        if( is_multisite() ){

            global $advert_network_options;
            global $get_network_type;
            global $main_site_id;
            global $current_site_id;

            if( $get_network_type === true && $current_site_id != $main_site_id && !array_key_exists('advert_active_campaigns_none',$advert_network_options) ){

            switch_to_blog($main_site_id);

            //check if current user is an advertiser
            $howmany = intval(apply_filters('check_advertiser', $user_id));

            if( $howmany === 0 )
                return;

            //top level page
            add_menu_page( __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), 'read', 'advert-user', '', '', 93.3);

            global $menu;

            $menu[93.3][2] = esc_url(admin_url('admin.php?page=advert-user'));

            //add user to main site if user does not have access with default subscriber role

            $role = 'subscriber';

            if(!is_user_member_of_blog( $user_id, $main_site_id )){

                    add_user_to_blog($main_site_id, $user_id, $role);

            }

            restore_current_blog();

            return;

            }

        }

        //top level page
        add_menu_page( __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), 'read', 'advert-user', 'advert_dashboard_user_start', '' );
            
        //change name of top level name in the submenu area
        add_submenu_page( 'advert-user', __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ), __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ), 'read', 'advert-user', 'advert_dashboard_user_start' );

        //add submenus
        if ( $howmany > 0 && current_user_can('edit_adverts') ){
            add_submenu_page( 'advert-user', __( 'Banners', 'ADVERT_TEXTDOMAIN' ), __( 'Banners', 'ADVERT_TEXTDOMAIN' ), 'edit_adverts', 'edit.php?post_type=advert-banner', '' );
            add_submenu_page( 'advert-user', __( 'Campaigns', 'ADVERT_TEXTDOMAIN' ), __( 'Campaigns', 'ADVERT_TEXTDOMAIN' ), 'edit_adverts', 'edit.php?post_type=advert-campaign', '' );

                $user_analysis = 0;
                if(array_key_exists('advert_allow_analysis_users', $advert_options)){
                    $user_analysis = intval($advert_options['advert_allow_analysis_users']);
                }

                //add submenus
                if($user_analysis === 1 && $howmany > 0){
                    add_submenu_page( 'advert-user', __( 'Analysis Overview', 'ADVERT_TEXTDOMAIN' ), __( 'Analysis', 'ADVERT_TEXTDOMAIN' ), 'edit_adverts', 'advert-analysis-overview', 'advert_load_analysis' );
                    add_submenu_page( 'advert-user', __( 'Analysis Drilldown', 'ADVERT_TEXTDOMAIN' ), __( 'Analysis', 'ADVERT_TEXTDOMAIN' ), 'edit_adverts', 'advert-analysis-drilldown', 'advert_load_analysis_drilldown' );                    
                }        
        
        }

    }

}


function register_advert_menu_transactions(){

    add_submenu_page('advert', __( 'Transactions', 'ADVERT_TEXTDOMAIN' ), __( 'Transactions', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-transactions', 'advert_load_transactions');

}


function register_advert_user_menu_transactions(){

    add_submenu_page('advert-user', __( 'Transactions', 'ADVERT_TEXTDOMAIN' ), __( 'Transactions', 'ADVERT_TEXTDOMAIN' ), 'edit_adverts', 'advert-transactions', 'advert_load_transactions');

}


function advert_load_transactions(){

    require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-transactions.php' );

}

        

function advert_load_dashboard(){

    require_once( ADVERT_PLUGIN_DIR . 'includes/advert-dashboard.php' );

}


function advert_load_analysis(){

    new AdVert_Analysis();

}

function advert_load_analysis_drilldown(){

    new AdVert_Analysis_Drilldown();

}

function advert_connected_multisite_css(){

wp_enqueue_style('advert-style', ADVERT_PLUGIN_URL . 'css/advertbe.css');

}