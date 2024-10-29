<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, hooks, admin and
 * public views.
 *
 * @since      1.0.0
 * @package    advert-for-wordpress
 * @subpackage advert-for-wordpress/includes
 * @author     Jeremy North
 */

class AdVert_For_Wordpress {

    protected $advert_version;

    public function __construct() {

        $this->advert_version = '1.0.5';
        $this->advert_get_payment_plugins();
        $this->advert_load_dependencies();

    }
    

    private function advert_load_dependencies() {
    

        if( is_multisite() ){

            global $advert_network_options;
            global $get_network_type;
            global $main_site_id;
            global $current_site_id;

        	require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-multisite.php' );
        	$advert_multisite = new AdVert_For_Wordpress_Multisite();

    	    //actions to take when creating or deleting a blog/site	    	 	    
    	    add_filter( 'wpmu_drop_tables', 'advert_multisite_delete_site' );
	        add_action( 'wpmu_new_blog', 'advert_multisite_create_site', 10, 6 );

		    // Check the type of advert network (see network setup)
	        $get_network_type = $advert_multisite::advert_network_check_type();

            // Gets Main Site ID or default site
	        $advert_defaul_site = '';
	        if( array_key_exists('advert_network_set_default_site',$advert_network_options) ){
		        $advert_defaul_site = intval($advert_network_options['advert_network_set_default_site']);
	        }

            if( $advert_defaul_site > 1 ){
                $main_site_id = $advert_defaul_site;
            }
            else{
                $main_site_id = $advert_multisite::advert_get_main_blog_id();                
            }

            // Gets Current Site ID
            $current_site_id  = get_current_blog_id();

            if( $get_network_type === true ){

                // Stores Networks Main Site Control Panel Options 
                global $advert_options;

                $advert_options1 = (is_array(get_blog_option($main_site_id, 'advert_cp_options_general')) ? get_blog_option($main_site_id, 'advert_cp_options_general') : []);
                $advert_options2 = (is_array(get_blog_option($main_site_id, 'advert_cp_options_users')) ? get_blog_option($main_site_id, 'advert_cp_options_users') : []);
                $advert_options3 = (is_array(get_blog_option($main_site_id, 'advert_cp_options_ads')) ? get_blog_option($main_site_id, 'advert_cp_options_ads') : []);
                $advert_options = array_merge($advert_options1, $advert_options2, $advert_options3);

            }
            elseif( $get_network_type === 'disabled' ){
            
                /** Catch the AdVert Shortcode and disable it if applicable  */
                add_shortcode( 'advert_location', array($this, 'advert_location_shortcode_disabled') );
                
                if( is_admin() ){

                    require_once( ADVERT_PLUGIN_DIR . 'admin/advert-admin-functions.php' );

                    //enqueue admin scripts
                    add_action('admin_enqueue_scripts', 'advert_admin_scripts');

                }

                return;

            }
            else{

                /** Stores Current Site Control Panel Options */
                global $advert_options;

                $advert_options1 = (is_array(get_option('advert_cp_options_general')) ? get_option('advert_cp_options_general') : []);
                $advert_options2 = (is_array(get_option('advert_cp_options_users')) ? get_option('advert_cp_options_users') : []);
                $advert_options3 = (is_array(get_option('advert_cp_options_ads')) ? get_option('advert_cp_options_ads') : []);
                $advert_options = array_merge($advert_options1, $advert_options2, $advert_options3);

            }

        }//end multisite    
        else{
    
            /** Stores Control Panel Options */
            global $advert_options;

            $advert_options1 = (is_array(get_option('advert_cp_options_general')) ? get_option('advert_cp_options_general') : []);
            $advert_options2 = (is_array(get_option('advert_cp_options_users')) ? get_option('advert_cp_options_users') : []);
            $advert_options3 = (is_array(get_option('advert_cp_options_ads')) ? get_option('advert_cp_options_ads') : []);
            $advert_options = array_merge($advert_options1, $advert_options2, $advert_options3);

        }

        //localization
        add_action( 'plugins_loaded', array($this, 'advert_load_textdomain') );

        //create new advert uploads folder if it does not exist
        add_action('init', array($this, 'advert_create_upload_dir'), 0);

        //advert payment plugins
        $payment_option = '';
        if(array_key_exists('advert_default_payments',$advert_options)){
            $payment_option = $advert_options['advert_default_payments'];
        }

        if($payment_option != 'none'  && $payment_option != ''){
            $plugdir = $payment_option . '/' . $payment_option . '.php';
            require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-control-panel.php' );
            require_once(ADVERT_PLUGIN_DIR . 'plugins/' . $plugdir);

        }

        //frontend
        if( !is_admin() || defined( 'DOING_AJAX' ) ){
            require_once(ADVERT_PLUGIN_DIR . 'public/advert-frontend.php');
        }

        //backend
        if(is_admin()){
            require_once(ADVERT_PLUGIN_DIR . 'admin/advert-admin.php');
        }

    }


    public function get_advert_version() {
        return $this->advert_version;
    }


    /**
     * Creates a new image upload directory - uploads/advert_uploads
     *
     */
    public function advert_create_upload_dir() {

        $upload_dir = wp_upload_dir();
        $upload_loc = $upload_dir['basedir']."/advert_uploads";

        if (!is_dir($upload_loc)) {
            wp_mkdir_p($upload_loc);
        }

    }


    public function advert_get_payment_plugins(){
        
        $dir   = ADVERT_PLUGIN_DIR . 'plugins/';
        $files = array_diff(scandir($dir), array('..', '.'));

        $advert_payment_plugins = array();

        foreach($files as $file){
            if(is_dir($dir.$file)){
                $advert_payment_plugins[] = $file;
            }
        }

        update_option('advert_payment_plugins', $advert_payment_plugins);

    }


    public function advert_load_textdomain() {

        load_plugin_textdomain( 'ADVERT_TEXTDOMAIN', false, dirname( plugin_basename( __FILE__ ) ) .'/languages' );

    }
    
    //this catches any advert_location shortcodes that are embeded and does not display them if using multisite and a site is disabled
    public function advert_location_shortcode_disabled($atts) {
    
	return;
	
    }

}// End 