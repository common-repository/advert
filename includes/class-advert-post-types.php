<?php

class AdVert_Post_Types {

    public function __construct() {

        //add new post status for campaigns, banners, advertisers and locations: advert-archive
        add_action('init', array($this, 'advert_post_status'));

        //register post types
        add_action('init', array($this, 'advert_banner_init'));
        add_action('init', array($this, 'advert_advertiser_init'));
        add_action('init', array($this, 'advert_campaign_init'));
        add_action('init', array($this, 'advert_location_init'));

        //adds a quick link to archive, removes quick edit and trash
        add_filter('post_row_actions', array($this, 'advert_replace_view_row_action'), 10, 1);

    }


    public function advert_post_status(){
	    register_post_status( 'advert-archive', array(
		    'label'                     => _x( 'Archive', 'post', 'ADVERT_TEXTDOMAIN' ),
		    'public'                    => false,
		    'exclude_from_search'       => true,
		    'show_in_admin_all_list'    => true,
		    'show_in_admin_status_list' => true,
		    'label_count'               => _n_noop( 'Archive <span class="count">(%s)</span>', 'Archives <span class="count">(%s)</span>', 'ADVERT_TEXTDOMAIN' ),
	    ) );
    }




    //register advert banner and banner categories
    public function advert_banner_init() {

    //check published: advertiser and location
    $user_id = get_current_user_id();
    $advertiser = intval(apply_filters('check_advertiser', $user_id));
    if ( $advertiser < 1 && !current_user_can('publish_adverts') ){return;}

    add_filter('post_updated_messages', 'banner_updated_messages');
    add_filter('manage_advert-banner_posts_columns', 'custom_banner_columns');
    add_filter('manage_edit-advert-banner_sortable_columns', 'banner_sortable_columns');
    add_action('manage_advert-banner_posts_custom_column', 'custom_banner_column', 10, 2);
    add_action('save_post', 'banner_save_meta');
    add_action('admin_notices', 'banner_admin_notice');

    global $wpdb;
    if ( taxonomy_exists('advert_category')) {
        return;
    }

    $labels = array(
		    'name'              => _x( 'Categories', 'taxonomy general name', 'ADVERT_TEXTDOMAIN' ),
		    'singular_name'     => _x( 'Categories', 'taxonomy singular name', 'ADVERT_TEXTDOMAIN' ),
		    'search_items'      => __( 'Search Categories', 'ADVERT_TEXTDOMAIN' ),
		    'all_items'         => __( 'All Categories', 'ADVERT_TEXTDOMAIN' ),
		    'parent_item'       => __( 'Parent Category', 'ADVERT_TEXTDOMAIN' ),
		    'parent_item_colon' => __( 'Parent Category:', 'ADVERT_TEXTDOMAIN' ),
		    'edit_item'         => __( 'Edit Category', 'ADVERT_TEXTDOMAIN' ),
		    'update_item'       => __( 'Update Category', 'ADVERT_TEXTDOMAIN' ),
		    'add_new_item'      => __( 'Add New Category', 'ADVERT_TEXTDOMAIN' ),
		    'new_item_name'     => __( 'New Category', 'ADVERT_TEXTDOMAIN' ),
		    'menu_name'         => __( 'Category', 'ADVERT_TEXTDOMAIN' ),
    );

    $args = array(
		    'hierarchical'      => true,
		    'labels'            => $labels,
		    'show_ui'           => true,
            'public'            => false,
            'rewrite'           => false,
            'query_var'         => false,
		    'show_admin_column' => true,
		    'query_var'         => true,
            'capabilities'      => array (
                                'manage_terms' => 'publish_adverts',
                                'edit_terms'   => 'publish_adverts',
                                'delete_terms' => 'publish_adverts',
                                'assign_terms' => 'edit_adverts'
                                ),
		    'rewrite'           => array( 'slug' => 'advert_category' ),
    );

    register_taxonomy( 'advert_category', array( 'advert-banner' ), $args );

    if (post_type_exists('advert-banner')) {
        return;
    }

    $labels = array(
      'name'               => _x( 'Banners', 'post type general name', 'ADVERT_TEXTDOMAIN' ),
      'singular_name'      => _x( 'Banner', 'post type singular name', 'ADVERT_TEXTDOMAIN' ),
      'menu_name'          => _x( 'Banners', 'admin menu', 'ADVERT_TEXTDOMAIN' ),
      'name_admin_bar'     => _x( 'Banner', 'add new on admin bar', 'ADVERT_TEXTDOMAIN' ),
      'add_new'            => _x( 'Add New', 'Banner', 'ADVERT_TEXTDOMAIN' ),
      'add_new_item'       => __( 'Add New Banner', 'ADVERT_TEXTDOMAIN' ),
      'new_item'           => __( 'New Banner', 'ADVERT_TEXTDOMAIN' ),
      'edit_item'          => __( 'Edit Banner', 'ADVERT_TEXTDOMAIN' ),
      'view_item'          => __( 'View Banner', 'ADVERT_TEXTDOMAIN' ),
      'all_items'          => __( 'All Banners', 'ADVERT_TEXTDOMAIN' ),
      'search_items'       => __( 'Search Banners', 'ADVERT_TEXTDOMAIN' ),
      'parent_item_colon'  => __( 'Parent Banners:', 'ADVERT_TEXTDOMAIN' ),
      'not_found'          => __( 'No Banners found.', 'ADVERT_TEXTDOMAIN' ),
      'not_found_in_trash' => __( 'No Banners found in Trash.', 'ADVERT_TEXTDOMAIN' )
    );

    $args = array(
      'labels'               => $labels,
      'public'               => false,
      'publicly_queryable'   => false,
      'show_ui'              => true,
      'show_in_menu'         => true,
      'query_var'            => false,
      'rewrite'              => false,
      'capability_type'      => 'advert',
      'map_meta_cap'         => true,
      'has_archive'          => false,
      'hierarchical'         => false,
      'menu_position'        => null,
      'exclude_from_search'  => true,
      'taxonomies'           => array('advert_category'),
      'register_meta_box_cb' => 'banner_start',
      'supports'             => array('title', 'thumbnail')
    );

    register_post_type( 'advert-banner', $args );

    }




    //register advert advertiser
    public function advert_advertiser_init() {

    if ( !current_user_can('publish_adverts') ){return;}

    add_filter('post_updated_messages', 'advertiser_updated_messages');
    add_action('save_post', 'advertiser_save_meta');
    add_action('before_delete_post', 'advertiser_delete_post');
    add_filter('manage_advert-advertiser_posts_columns', 'custom_advertiser_columns');
    add_action('manage_advert-advertiser_posts_custom_column', 'custom_advertiser_column', 10, 2);
    add_filter('manage_edit-advert-advertiser_sortable_columns', 'custom_advertiser_sortable_columns');

    global $wpdb;
    if (post_type_exists('advert-advertiser')) {
        return;
    }

    $labels = array(
      'name'               => _x( 'Advertisers', 'post type general name', 'ADVERT_TEXTDOMAIN' ),
      'singular_name'      => _x( 'Advertiser', 'post type singular name', 'ADVERT_TEXTDOMAIN' ),
      'menu_name'          => _x( 'Advertisers', 'admin menu', 'ADVERT_TEXTDOMAIN' ),
      'name_admin_bar'     => _x( 'Advertiser', 'add new on admin bar', 'ADVERT_TEXTDOMAIN' ),
      'add_new'            => _x( 'Add New', 'Advertiser', 'ADVERT_TEXTDOMAIN' ),
      'add_new_item'       => __( 'Add New Advertiser', 'ADVERT_TEXTDOMAIN' ),
      'new_item'           => __( 'New Advertiser', 'ADVERT_TEXTDOMAIN' ),
      'edit_item'          => __( 'Edit Advertiser', 'ADVERT_TEXTDOMAIN' ),
      'view_item'          => __( 'View Advertiser', 'ADVERT_TEXTDOMAIN' ),
      'all_items'          => __( 'All Advertisers', 'ADVERT_TEXTDOMAIN' ),
      'search_items'       => __( 'Search Advertisers', 'ADVERT_TEXTDOMAIN' ),
      'parent_item_colon'  => __( 'Parent Advertisers:', 'ADVERT_TEXTDOMAIN' ),
      'not_found'          => __( 'No Advertisers found.', 'ADVERT_TEXTDOMAIN' ),
      'not_found_in_trash' => __( 'No Advertisers found in Trash.', 'ADVERT_TEXTDOMAIN' )
    );

    $args = array(
      'labels'               => $labels,
      'public'               => false,
      'publicly_queryable'   => false,
      'show_ui'              => true,
      'show_in_menu'         => false,
      'query_var'            => false,
      'rewrite'              => false,
      'capability_type'      => 'advert',
      'map_meta_cap'         => true,
      'has_archive'          => false,
      'hierarchical'         => false,
      'menu_position'        => null,
      'exclude_from_search'  => true,
      'register_meta_box_cb' => 'advertiser_start',
      'supports'             => false
    );

    register_post_type( 'advert-advertiser', $args );

    }


    //register advert campaign
    public function advert_campaign_init() {

    //check published: advertiser and location
    $user_id = get_current_user_id();
    $advertiser = intval(apply_filters('check_advertiser', $user_id));
    if ( $advertiser < 1 && !current_user_can('publish_adverts') ){return;}

    add_filter('post_updated_messages', 'campaign_updated_messages');
    add_action('save_post', 'campaign_save_meta');
    add_filter('manage_advert-campaign_posts_columns', 'custom_campaign_columns');
    add_action('manage_advert-campaign_posts_custom_column', 'custom_campaign_column', 10, 2);
    add_filter('manage_edit-advert-campaign_sortable_columns', 'campaign_sortable_columns');

    global $wpdb;
    if (post_type_exists('advert-campaign')) {
        return;
    }

    $labels = array(
      'name'               => _x( 'Campaigns', 'post type general name', 'ADVERT_TEXTDOMAIN' ),
      'singular_name'      => _x( 'Campaign', 'post type singular name', 'ADVERT_TEXTDOMAIN' ),
      'menu_name'          => _x( 'Campaigns', 'admin menu', 'ADVERT_TEXTDOMAIN' ),
      'name_admin_bar'     => _x( 'Campaign', 'add new on admin bar', 'ADVERT_TEXTDOMAIN' ),
      'add_new'            => _x( 'Add New', 'Campaign', 'ADVERT_TEXTDOMAIN' ),
      'add_new_item'       => __( 'Add New Campaign', 'ADVERT_TEXTDOMAIN' ),
      'new_item'           => __( 'New Campaign', 'ADVERT_TEXTDOMAIN' ),
      'edit_item'          => __( 'Edit Campaign', 'ADVERT_TEXTDOMAIN' ),
      'view_item'          => __( 'View Campaign', 'ADVERT_TEXTDOMAIN' ),
      'all_items'          => __( 'All Campaigns', 'ADVERT_TEXTDOMAIN' ),
      'search_items'       => __( 'Search Campaigns', 'ADVERT_TEXTDOMAIN' ),
      'parent_item_colon'  => __( 'Parent Campaigns:', 'ADVERT_TEXTDOMAIN' ),
      'not_found'          => __( 'No Campaigns found.', 'ADVERT_TEXTDOMAIN' ),
      'not_found_in_trash' => __( 'No Campaigns found in Trash.', 'ADVERT_TEXTDOMAIN' )
    );

    $args = array(
      'labels'               => $labels,
      'public'               => false,
      'publicly_queryable'   => false,
      'show_ui'              => true,
      'show_in_menu'         => true,
      'query_var'            => false,
      'rewrite'              => false,
      'capability_type'      => 'advert',
      'map_meta_cap'         => true,
      'has_archive'          => false,
      'hierarchical'         => false,
      'menu_position'        => null,
      'exclude_from_search'  => true,
      'register_meta_box_cb' => 'campaign_start',
      'supports'             => array('title', 'thumbnail')
    );

    register_post_type( 'advert-campaign', $args );

    }


    //register advert location
    public function advert_location_init() {

    global $advert_options;

    $allow_loc_edit = 0;
    if(array_key_exists('advert_advertmgr_add_location', $advert_options)){
        $allow_loc_edit = intval($advert_options['advert_advertmgr_add_location']);
    }

    if( $allow_loc_edit === 1 || current_user_can('manage_options') ){
        $allow_loc_edit = true;
    }
    elseif( current_user_can('publish_adverts') ){
        $allow_loc_edit = false;   
    }
    else{
        $allow_loc_edit = true;     
    }

    add_filter('post_updated_messages', 'location_updated_messages');
    add_action( 'save_post', 'location_save_meta');
    add_filter('manage_advert-location_posts_columns', 'custom_location_columns');
    add_action('manage_advert-location_posts_custom_column', 'custom_location_column', 10, 2);
    add_filter('manage_edit-advert-location_sortable_columns', 'location_sortable_columns');

    $labels = array(
      'name'               => _x( 'Locations', 'post type general name', 'ADVERT_TEXTDOMAIN' ),
      'singular_name'      => _x( 'Location', 'post type singular name', 'ADVERT_TEXTDOMAIN' ),
      'menu_name'          => _x( 'Locations', 'admin menu', 'ADVERT_TEXTDOMAIN' ),
      'name_admin_bar'     => _x( 'Location', 'add new on admin bar', 'ADVERT_TEXTDOMAIN' ),
      'add_new'            => _x( 'Add New', 'Location', 'ADVERT_TEXTDOMAIN' ),
      'add_new_item'       => __( 'Add New Location', 'ADVERT_TEXTDOMAIN' ),
      'new_item'           => __( 'New Location', 'ADVERT_TEXTDOMAIN' ),
      'edit_item'          => __( 'Edit Location', 'ADVERT_TEXTDOMAIN' ),
      'view_item'          => __( 'View Location', 'ADVERT_TEXTDOMAIN' ),
      'all_items'          => __( 'All Locations', 'ADVERT_TEXTDOMAIN' ),
      'search_items'       => __( 'Search Locations', 'ADVERT_TEXTDOMAIN' ),
      'parent_item_colon'  => __( 'Parent Locations:', 'ADVERT_TEXTDOMAIN' ),
      'not_found'          => __( 'No locations found.', 'ADVERT_TEXTDOMAIN' ),
      'not_found_in_trash' => __( 'No locations found in Trash.', 'ADVERT_TEXTDOMAIN' )
    );

    $args = array(
      'labels'               => $labels,
      'public'               => false,
      'publicly_queryable'   => false,
      'show_ui'              => true,
      'show_in_menu'         => $allow_loc_edit,
      'query_var'            => false,
      'rewrite'              => false,
      'capability_type'      => 'advert',
      'map_meta_cap'         => true,
      'has_archive'          => false,
      'hierarchical'         => false,
      'menu_position'        => null,
      'exclude_from_search'  => true,
      'register_meta_box_cb' => 'location_start',
      'supports'             => array('title', 'thumbnail')
    );

    register_post_type( 'advert-location', $args );

    }


    public function advert_replace_view_row_action( $actions ) {

    if( get_post_type() === 'advert-banner' || get_post_type() === 'advert-advertiser' || get_post_type() === 'advert-campaign' || get_post_type() === 'advert-location' ){
        unset( $actions['inline hide-if-no-js'] );

        global $advert_options;

        $advert_allow_analysis_users = 0;
        if(array_key_exists('advert_allow_analysis_users', $advert_options)){
            $advert_allow_analysis_users = intval($advert_options['advert_allow_analysis_users']);
        }

        if(get_post_type() != 'advert-location' && $advert_allow_analysis_users === 1 || current_user_can('publish_adverts')){
            $nonce = wp_create_nonce( 'advert-analysis-link' );
            $actions['analysis_link'] = '<a href="'.esc_url(admin_url('admin.php?page=advert-analysis-drilldown')).'&amp;post=analysis&amp;advert-analysis-'.str_replace('advert-', '', get_post_type()).'='.get_the_ID().'&amp;_wpnonce='.$nonce.'">' . __( 'Analysis', 'ADVERT_TEXTDOMAIN' ) . '</a>';
        }
    }
        if(current_user_can('publish_adverts')){
            if( get_post_type() === 'advert-banner' || get_post_type() === 'advert-advertiser' || get_post_type() === 'advert-campaign' ){
                if (get_post_status() != 'advert-archive' ){
                    $nonce = wp_create_nonce( 'advert-archive' );
                    $actions['archive_post'] = '<a href="admin.php?page=postdata&amp;noheader=true&amp;post='.get_the_ID().'&amp;action=archive&amp;_wpnonce='.$nonce.'">' . __( 'Archive', 'ADVERT_TEXTDOMAIN' ) . '</a>';
                }
                else{
                    $nonce = wp_create_nonce( 'advert-archive' );
                    $actions['archive_post'] = '<a href="admin.php?page=postdata&amp;noheader=true&amp;post='.get_the_ID().'&amp;action=unarchive&amp;_wpnonce='.$nonce.'">' . __( 'Unarchive', 'ADVERT_TEXTDOMAIN' ) . '</a>'; 
                }
            }
        }
    return $actions;
    }


}// End AdVert Post Types Class