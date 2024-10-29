<?php
/**
* AdVert configuration file - Install, Deactivate or Uninstall AdVert related stuff
*
* @since 1.0.0
*
* @package AdVert
* @package advert-setup
*/

//AdVert Install
function advert_activate() {

    if( is_multisite() ){

        global $wpdb;

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            advert_do_activate();
            restore_current_blog();
        }

    }
    else{
        
        advert_do_activate();

    }

}


function advert_do_activate(){

    /** Check if current user can activate plugins */
    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    /** Create the AdVert table if it doesnt already exist - advert_logged */
    $table_name = $wpdb->prefix . 'advert_logged';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	        id BIGINT NOT NULL AUTO_INCREMENT,
            ip_address VARCHAR(255) NOT NULL,
            browser VARCHAR(255) NOT NULL,
            device VARCHAR(255) NOT NULL,
            typeof VARCHAR(255) NOT NULL,
            location_id INT NOT NULL,
            camp_id INT NOT NULL,
            banner_id INT NOT NULL,
            adv_id INT NOT NULL,
            price DECIMAL(18,2) NOT NULL,
            time DATETIME NOT NULL,
	        url VARCHAR(255) DEFAULT '' NOT NULL,
            feedback INT(2) NULL,
            location_name VARCHAR(255) NOT NULL,
            adv_name VARCHAR(255) NOT NULL,
            camp_name VARCHAR(255) NOT NULL,
            camp_type VARCHAR(3) NOT NULL,
            banner_name VARCHAR(255) NOT NULL,
            banner_type VARCHAR(255) NOT NULL,
	        UNIQUE KEY id (id)
	    ) $charset_collate;";

    dbDelta( $sql );

    /** Create the AdVert payment table if it doesnt already exist - advert_payment */
    $table_name = $wpdb->prefix . 'advert_payment';

    $sql2 = "CREATE TABLE IF NOT EXISTS $table_name (
	        id BIGINT NOT NULL AUTO_INCREMENT,
            adv_id INT NOT NULL,
            adv_name VARCHAR(255) NOT NULL,
            added DECIMAL(18,2) NOT NULL,
            removed DECIMAL(18,2) NOT NULL,
            time DATETIME NOT NULL,
            reason VARCHAR(255) NULL,
            trans_id VARCHAR(255) NULL,
	        UNIQUE KEY id (id)
	    ) $charset_collate;";

    dbDelta( $sql2 );


    /** Set AdVert database version */
    $advert_db_version = '1.0';
    add_option( 'advert_db_version', $advert_db_version );


    //create startup options on install
    /**
        * Create two roles Advertiser and AdVert Manager. With the new roles created, add capabilities to both roles and the admin role
        *
        * @since 1.0.0
        */

    //create new roles
    global $wp_roles;
    $wp_roles = new WP_Roles();

    $wp_roles->add_role( 'advert_user', __( 'Advertiser', 'ADVERT_TEXTDOMAIN' ),
        array(
        'read' => true,
        'upload_files' => true,
        )
    );

    $wp_roles->add_role( 'advert_manager', __( 'AdVert Manager', 'ADVERT_TEXTDOMAIN' ),
        array(
        'read' => true,
        'upload_files' => true,
        )
    );



    // add the capabilities to advertiser
    $adv_caps = array(
    'edit_adverts',
    'edit_published_adverts',
    );

    foreach ( $adv_caps as $cap ) {
    $wp_roles->add_cap( 'advert_user', $cap );
    }



    // add the capabilities to AdVert Manager
    $adv_mgr_caps = array(
    'edit_adverts',
    'edit_others_adverts',
    'edit_published_adverts',
    'edit_private_adverts',
    'read_private_adverts',
    'publish_adverts',
    );

    foreach ( $adv_mgr_caps as $cap ) {
    $wp_roles->add_cap( 'advert_manager', $cap );
    }


    // add the capabilities to Admin
    $adv_admin_caps = array(
    'edit_adverts',
    'delete_adverts',
    'edit_others_adverts',
    'delete_others_adverts',
    'edit_published_adverts',
    'delete_published_adverts',
    'edit_private_adverts',
    'delete_private_adverts',
    'read_private_adverts',
    'publish_adverts',
    );

    foreach ( $adv_admin_caps as $cap ) {
    $wp_roles->add_cap( 'administrator', $cap );
    }

    /** Adds an option to redirect to AdVert welcome page on install */
    add_option('advert_do_activation_redirect', true);


    /** Create standard locations */
    if( !get_option('advert_standard_locations') ) {
        create_standard_locations();
    }

}//end AdVert install


//AdVert Uninstall
function advert_uninstall() {

    if( is_multisite() ){

        global $wpdb;

        /** Removes Option Group from Network */
        unregister_setting('advert_network_setup', 'advert_network_setup');
        delete_option('advert_network_setup');

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            advert_do_uninstall();
            restore_current_blog();
        }

    }
    else{
        
        advert_do_uninstall();

    }

}

function advert_do_uninstall() {

    /** Check if current user can activate plugins */
    if ( ! current_user_can( 'activate_plugins' ) )
        exit();

    /** Adds an option to see if AdVert was previously installed */
    add_option('advert_previously_installed', 'Yes');


    //remove advert-categories
    //wp_delete_object_term_relationships( $object_id, $taxonomies );


    /**
        * Remove advert posts (banner, campaign, location, advertiser)
        * Deletes the posts permanently
        *
        * @since 1.0.0
        */
    $advert_post_types = get_posts(array( 'post_type' => array( 'advert-banner', 'advert-campaign', 'advert-location', 'advert-advertiser' ), 'posts_per_page' => -1, 'post_status' => 'any' ));
        foreach( $advert_post_types as $post_type ) {
            wp_delete_post( $post_type->ID, true);
        }

    /** Removes advertuser meta */
    $args = array('meta_key' => 'advert_advertiser_company_id');
    $users = get_users($args);

    foreach ($users as $user){
        delete_user_meta($user->ID, 'advert_advertiser_company_id');
        delete_user_meta($user->ID, 'show_advert_welcome_panel');
    }


    /** Removes Option Groups and other options - Control Panel Options */
    unregister_setting('advert_cp_options_general', 'advert_cp_options_general');
    unregister_setting('advert_cp_options_users', 'advert_cp_options_users');
    unregister_setting('advert_cp_options_ads', 'advert_cp_options_ads');

    delete_option('advert_standard_locations');
    delete_option('advert_db_version');

    delete_option('advert_payment_plugins');
    delete_option('advert_do_activation_redirect');
    delete_option('advert_cp_options_general');
    delete_option('advert_cp_options_users');
    delete_option('advert_cp_options_ads');
    delete_option('widget_advert_widget');


    //Removes AdVert roles - Advertiser and AdVert Manager
    global $wp_roles;
    $wp_roles->remove_role("advert_user");
    $wp_roles->remove_role("advert_manager");


    /** Removes the AdVert table if it exists - advert_logged */
    global $wpdb;
    $wpdb->query( $wpdb->prepare("DROP TABLE IF EXISTS {$wpdb->prefix}advert_logged") );
    $wpdb->query( $wpdb->prepare("DROP TABLE IF EXISTS {$wpdb->prefix}advert_payment") );


    /** Removes the AdVert image upload directory - uploads/advert_uploads */
    $upload_dir = wp_upload_dir();
    $upload_loc = $upload_dir['basedir']."/advert_uploads";
    if (is_dir($upload_loc)) {
        rmdir($upload_loc);
    }

}//end AdVert uninstall


//AdVert deactivation
function advert_deactivate() {

    if( is_multisite() ){

        global $wpdb;

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            advert_do_deactivate();
            restore_current_blog();
        }

    }
    else{
        
        advert_do_deactivate();

    }

}


function advert_do_deactivate(){

    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    global $wp_roles;



    // remove the capabilities from Advertiser
    $adv_caps = array(
    'edit_adverts',
    'delete_adverts',
    'edit_published_adverts',
    'delete_published_adverts',
    );

    foreach ( $adv_caps as $cap ) {
    $wp_roles->remove_cap( 'advert_user', $cap );
    }



    // remove the capabilities from AdVert Manager
    $adv_mgr_caps = array(
    'edit_adverts',
    'delete_adverts',
    'edit_others_adverts',
    'delete_others_adverts',
    'edit_published_adverts',
    'delete_published_adverts',
    'edit_private_adverts',
    'delete_private_adverts',
    'read_private_adverts',
    'publish_adverts',
    );

    foreach ( $adv_mgr_caps as $cap ) {
    $wp_roles->remove_cap( 'advert_manager', $cap );
    }



    // remove the capabilities from Admin
    $adv_admin_caps = array(
    'edit_adverts',
    'delete_adverts',
    'edit_others_adverts',
    'delete_others_adverts',
    'edit_published_adverts',
    'delete_published_adverts',
    'edit_private_adverts',
    'delete_private_adverts',
    'read_private_adverts',
    'publish_adverts',
    );

    foreach ( $adv_admin_caps as $cap ) {
    $wp_roles->remove_cap( 'administrator', $cap );
    }

}//end AdVert deactivation


function create_standard_locations(){

$before_content = array(
    'post_title'    => __( 'Before Content', 'ADVERT_TEXTDOMAIN' ),
    'post_name'     => __( 'before_content', 'ADVERT_TEXTDOMAIN' ),
    'post_type'     => 'advert-location',
    'post_status'   => 'pending'
);
$before_content = wp_insert_post( $before_content );

$after_content = array(
    'post_title'    => __( 'After Content', 'ADVERT_TEXTDOMAIN' ),
    'post_name'     => __( 'after_content', 'ADVERT_TEXTDOMAIN' ),
    'post_type'     => 'advert-location',
    'post_status'   => 'pending'
);
$after_content = wp_insert_post( $after_content );

/** Adds an option for standard location retrieval */
$standard_locations = array (
    'beforecontent' => $before_content,
    'aftercontent'  => $after_content
);

add_option('advert_standard_locations', $standard_locations);

}