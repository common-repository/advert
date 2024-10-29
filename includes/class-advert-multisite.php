<?php

class AdVert_For_Wordpress_Multisite {

	public function __construct() {

	    $this->advert_multisite_start();

	}
	
	
	public function advert_multisite_start(){

	    global $advert_network_options;
	        
	    $main_site_id = $this->advert_get_main_blog_id();
	        
	    $advert_network_options = (is_array(get_blog_option($main_site_id, 'advert_network_setup')) ? get_blog_option($main_site_id, 'advert_network_setup') : []);


        //add network menu
        if(is_admin()){
    
    	    //add_action('plugins_loaded', array($this, 'advert_multisite_network_menus'));
            add_action('network_admin_menu', array($this, 'advert_create_network_menu'));
		    add_action( 'admin_init', array( $this, 'page_init' ) );
    	        
    	}		
	
	}

	
	public function advert_create_network_menu(){

	    //network level advert page
	    add_menu_page( __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), __( 'AdVert', 'ADVERT_TEXTDOMAIN' ), 'manage_network', 'advert', array( $this, 'create_network_setup'), '' );
	
	    //network level advert page submenus
	    add_submenu_page('advert', __( 'AdVert Network Setup', 'ADVERT_TEXTDOMAIN' ), __( 'Network Setup', 'ADVERT_TEXTDOMAIN' ), 'manage_network', 'advert', array( $this, 'create_network_setup'));
	        	
	}

	
	public function create_network_setup(){
	

	        // Set class property
	        $this->options = get_option( 'advert_network_setup' );
	
	        //display settings messages
	        settings_errors();
	
	        //get current screen and set option name
	        global $currentScreen;
	        $currentScreen = '_network_setup';
	
	        ?>
	
	        <div class="wrap advert-settings-wrap">
	
	        <?php screen_icon(); ?>
	
	        <div class="advert-page-heading-logo">a</div>
	
	        <h1 class="advert-heading-tag"><?php _e( 'Network Setup', 'ADVERT_TEXTDOMAIN' ); ?></h1>
            <h2 class="dummy-h2"></h2>

	        <div id="advert-control-panel" class="wrap">
	
	        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
	        <div class="advert-additional-wrap">
	
	        <?php
	
	        settings_fields( 'advert_network_setup' );   
	        do_settings_sections( 'advert-network-setup' );
	        ?>
	        </div>
	        <?php submit_button(); ?>
	        </form>
	
	        <div class="advert-donate-wrap">
	        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
	        <input type="hidden" name="cmd" value="_s-xclick">
	        <input type="hidden" name="hosted_button_id" value="NPK8ETF9PAKPN">
	        <input type="image" class="advert-donate-button" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	        </form>
	        </div>
	
	        <div class="clear"></div>
	
	        <p class="advert-donate-text">
	        <?php _e('If you enjoy using AdVert, consider donating for continued plugin support and updates. If you are not able to make a donation right now, thats ok. You can <a href="https://wordpress.org/support/view/plugin-reviews/advert/" target="_blank">Rate AdVert</a>, enable the AdVert link in the Control Panel or tell people about AdVert for WordPress. AdVert will never offer a premium or pro version.', 'ADVERT_TEXTDOMAIN'); ?>
	        </p>
	
	        </div>
	        </div>
	
	        <?php

	
	}	
	
	
	
    public function page_init() {

        global $advert_network_options;      

        register_setting('advert_network_setup', 'advert_network_setup', array( $this, 'advert_options_validate' ));

	//get options
        $allow_sites_network_option = '';
        if( array_key_exists('advert_network_allow_sites',$advert_network_options) ){
            $allow_sites_network_option = $advert_network_options['advert_network_allow_sites'];
        }

        $disabled_sites_network_option = '';
        if( array_key_exists('advert_network_disable_independent_sites',$advert_network_options) ){
            $disabled_sites_network_option = true;
        }

        //network setup
        add_settings_section(
        'advert_setup_options_s1', // ID
        __( 'Type of AdVert Network', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_setup_s1' ), // Callback
        'advert-network-setup' // Page
        );

        add_settings_field(
        'advert_network_allow_sites', // ID
        __( 'How do you want to use AdVert on Multisite', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'select_callback' ), // Callback
        'advert-network-setup', // Page
        'advert_setup_options_s1', // Section
        $args = array ('label_for' => 'advert_network_allow_sites', 'option1' => __('Connected AdVert Network', 'ADVERT_TEXTDOMAIN'), 'option2' => __('Independent AdVert Network', 'ADVERT_TEXTDOMAIN'))
        );

        $default_site = $this->advert_get_main_blog_id();
        $blog_details = get_blog_details($default_site);

        add_settings_field(
        'advert_network_set_default_site', // ID
        __( 'Set default site for your network (optional)', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'textbox_callback' ), // Callback
        'advert-network-setup', // Page
        'advert_setup_options_s1', // Section
        $args = array ('label_for' => 'advert_network_set_default_site', 'type' => 'number', 'minvalue' => 1, 'text'=> sprintf( __('The default site will always be %s (%s) unless you add a different default site. If using a Connected AdVert Network, your default site will be used to manage advertisements and settings across all sites.', 'ADVERT_TEXTDOMAIN'), $blog_details->blogname, $blog_details->blog_id))
        );

	if( $allow_sites_network_option == 'Independent AdVert Network' ){
	
        add_settings_field(
        'advert_network_disable_independent_sites', // ID
        __( 'Disable AdVert for the following sites', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'independent_checkbox_callback' ), // Callback
        'advert-network-setup', // Page
        'advert_setup_options_s1', // Section
        $args = array ('label_for' => 'advert_network_disable_independent_sites')
        );	

	    if( $disabled_sites_network_option ){

	            add_settings_field(
	            'advert_network_control_disabled_independent_sites', // ID
	            __( 'Use Connected AdVert Network for sites that are disabled', 'ADVERT_TEXTDOMAIN' ), // Title
	            array( $this, 'checkbox_callback' ), // Callback
	            'advert-network-setup', // Page
	            'advert_setup_options_s1', // Section
	            $args = array ('label_for' => 'advert_network_control_disabled_independent_sites')
	            );	
        
            }
        
        add_settings_field(
        'advert_network_empty_independent_sites', // ID
        __( 'Use Connected AdVert Network for sites that have no active campiagns', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'checkbox_callback' ), // Callback
        'advert-network-setup', // Page
        'advert_setup_options_s1', // Section
        $args = array ('label_for' => 'advert_network_empty_independent_sites')
        );	        
	
	}//end if independent advert network
	
    }





    //infobox
    public function print_section_info_network_s1() {
        echo '<span>' . __( 'Choose the type of AdVert Network', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_setup_s1() {
        echo '<span>' . __( 'Choose the type of AdVert Network for all sites', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }





    // Textboxes
    public function textbox_callback(array $args) {

        global $currentScreen;
        $textbox_id = $args['label_for'];
        $type = array_key_exists('type', $args) ? $args['type'] : 'text';
        $text = ( array_key_exists('text', $args) ? '<br /><p>' . $args['text'] . '</p>' : '' );
        $textbox_options = get_option('advert'.$currentScreen);
        $minValue = ( array_key_exists('minvalue', $args) ? 'min="' . $args['minvalue'] . '"' : '' );
        printf(
        '<input type="'.$type.'" id="'.$textbox_id.'" name="advert'.$currentScreen.'['.$textbox_id.']" '.$minValue.' value="%s" />&nbsp;'.$text,
        isset( $textbox_options[$textbox_id] ) ? esc_attr( $textbox_options[$textbox_id] ) : ''
        );            

    }

    // Textarea
    public function textarea_callback(array $args) {

        global $currentScreen;
        $textarea_id = $args['label_for'];
        $text = ( array_key_exists('text', $args) ? '<br /><p>' . $args['text'] . '</p>' : '' );
        $textarea_options = get_option('advert'.$currentScreen);
        printf(
        '<textarea rows="4" cols="50" id="'.$textarea_id.'" name="advert'.$currentScreen.'['.$textarea_id.']">%s</textarea>'.$text,
        isset( $textarea_options[$textarea_id] ) ? esc_attr( $textarea_options[$textarea_id] ) : ''
        );

    }

    // Selection
    public function select_callback(array $selectOptions) {

        global $currentScreen;
        $select_id = $selectOptions['label_for'];
        $text = ( array_key_exists('text', $selectOptions) ? '<span class="advert-span-break"></span><span class="advert-span-info">' . $args['text'] . '</span>' : '' );
        $select_options = get_option('advert'.$currentScreen);
        $howmany = sizeof($selectOptions);
        $count = 1;
        $selected = '';
        $html = '<select id="'.$select_id.'" name="advert'.$currentScreen.'['.$select_id.']">';
        while ($count < $howmany) {
        if(!empty($select_options[$select_id])){
        $selected = selected( $select_options[$select_id], $selectOptions['option'.$count], false);
        }
        $html .= '<option value="'.$selectOptions['option'.$count].'" '.$selected.'>'.$selectOptions['option'.$count].'</option>';
        $count = $count + 1;
        }
        $html .= '</select>';
        echo $html.$text;

    }

    // Checkbox
    public function checkbox_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
        $checked = ( isset($checkbox_options[$checkbox_id]) && intval($checkbox_options[$checkbox_id]) === 1 ? $checked = 1 : $checked = 0 );
        $text = ( array_key_exists('text', $args) ? '<span class="advert-span-break"></span><span class="advert-span-info">' . $args['text'] . '</span>' : '' );
        $warning = ( array_key_exists('warning', $args) ? '<span class="advert-span-break"></span><span class="advert-cp-warning-msg advert-span-info">' . $args['warning'] . '</span>' : '' );
        $warningClass = ( !empty($warning) ? $warningClass = 'class="hide-if-no-js advert-cp-warning"' : '' );
        $html = '<input type="checkbox" id="'.$checkbox_id.'" ' . $warningClass . ' name="advert'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ) .' />';
        echo $html.$text.$warning;

    }

    // Radio
    public function radio_callback(array $radioOptions) {

        global $currentScreen;
        $radio_id = $radioOptions['label_for'];
        $text = ( array_key_exists('text', $args) ? '<br /><p>' . $args['text'] . '</p>' : '' );
        $radio_options = get_option('advert'.$currentScreen);
        $howmany = sizeof($radioOptions);
        $count = 1;
        $html = '';
        while ($count < $howmany) {
        $html .= '<label for="'.$radio_id.$count.'">'.$radioOptions['radio'.$count].'<input type="radio" id="'.$radio_id.$count.'" name="advert'.$currentScreen.'['.$radio_id.']" value="'.$count.'" '. checked( $count, $radio_options[$radio_id], false ).' style="margin-left:5px;" /></label><br>';
        $count = $count + 1;
        }
        echo $html.$text;

    }


    public function restore_standard_locations_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
        if(isset($checkbox_options[$checkbox_id]) && $checkbox_options[$checkbox_id] === '1' && current_user_can('manage_options')){
            $standard_locations = get_option('advert_standard_locations');
            if(is_array($standard_locations)){
                foreach($standard_locations as $key => $location){                   
                    if(get_post_status($location) && get_post_type($location) === 'advert-location' ){
                        wp_delete_post($location, true);
                    }
                }
            }
            delete_option('advert_standard_locations');
            require_once(ADVERT_PLUGIN_DIR . 'includes/class-advert-setup.php');
                AdVert_Setup::create_standard_locations();
            $text = '<span class="advert-span-break"></span><span class="advert-span-info">' . __( 'Standard Locations have been restored', 'ADVERT_TEXTDOMAIN' ) . '</span>';
            $warning = '';
            $warningClass = '';
            $checked = 0;
        }
        else{
            $checked = 0;
            $text = ( array_key_exists('text', $args) ? '<span class="advert-span-break"></span><span class="advert-span-info">' . $args['text'] . '</span>' : '' );
            $warning = ( array_key_exists('warning', $args) ? '<span class="advert-span-break"></span><span class="advert-cp-warning-msg advert-span-info">' . $args['warning'] . '</span>' : '' );
            $warningClass = ( !empty($warning) ? $warningClass = 'class="hide-if-no-js advert-cp-warning"' : '' );
        }
        $html = '<input type="checkbox" id="'.$checkbox_id.'" ' . $warningClass . ' name="advert'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ).' />';
        echo $html.$text.$warning;
    }


    // Checkbox specific to empty advert database
    public function empty_advert_db_checkbox_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
        if(isset($checkbox_options[$checkbox_id]) && $checkbox_options[$checkbox_id] === '1' && current_user_can('manage_options')){
        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_logged';
        $delete = $wpdb->query( "TRUNCATE TABLE $table_name " );
        $checked = 0;
        }
        else{$checked = 0;}
        $warning = '<span class="advert-span-break"></span><span class="advert-cp-warning-msg advert-span-info" class="advert-language-js">' . __( 'Warning: Selecting this option will remove all the data from the AdVert database table. Its recomended that you backup your database before continuing.', 'ADVERT_TEXTDOMAIN') . '</span>';
        $html = '<input type="checkbox" id="'.$checkbox_id.'" class="hide-if-no-js advert-cp-warning" name="advert'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ).' />';
        echo $html.$warning;

    }


    // Checkbox post type
    public function checkbox_post_type_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
                 
        $post_args = array(
           'public'   => true
        );

        $post_types = get_post_types( $post_args ); 
        
        echo '<div class="advert-multicheckbox">';

        foreach( $post_types as $type){

            $checked = ( isset($checkbox_options[$checkbox_id.'-'.$type]) && intval($checkbox_options[$checkbox_id.'-'.$type]) === 1 ? $checked = 1 : $checked = 0 );        
            echo '<p><label for="'.$checkbox_id.'-'.$type.'">';
            echo '<input type="checkbox" id="'.$checkbox_id.'-'.$type.'" name="advert'.$currentScreen.'['.$checkbox_id.'-'.$type.']" value="1" '. checked( 1, $checked, false ) .' />'.$type.'</label></p>';
            
        }    

        echo '<div>';

    }

    // Checkbox post type
    public function independent_checkbox_callback(array $args) {

        global $currentScreen;
        global $wpdb;
        
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
                 
	// Get all blogs in the network and activate plugin on each one
	$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        
        echo '<div class="advert-multicheckbox"><ul>';
        
        foreach( $blogs as $blog ){
        
            if(!empty($checkbox_options[$checkbox_id])){
            $checked = ( in_array($blog, $checkbox_options[$checkbox_id]) ? $checked = 1 : $checked = 0 );
            }
            else{
            $checked = '';
            }
            
            $blog_details = get_blog_details($blog);
	    echo '<li><label for="advert'.$currentScreen.'['.$checkbox_id.']['.$blog.']"><input type="checkbox" id="advert'.$currentScreen.'['.$checkbox_id.']['.$blog.']" name="advert'.$currentScreen.'['.$checkbox_id.'][]" value="'.$blog.'" title="'.$blog_details->siteurl.'" '. checked( 1, $checked, false ) .'>'.$blog_details->blogname.'</label></li>';    

        }   
        
        echo '</ul><div>';

    }

    // Selectbox post type
    public function independent_select_callback(array $args) {

        global $currentScreen;
        global $wpdb;
        
        $selectbox_id = $args['label_for'];
        $selectbox_options = get_option('advert'.$currentScreen);
                 
	// Get all blogs in the network and activate plugin on each one
	$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        
        echo '<div class="advert-multiselectbox">';
	echo '<select multiple id="'.$selectbox_id.'" name="advert'.$currentScreen.'['.$selectbox_id.'][]">';

        foreach( $blogs as $blog ){
        
            if(!empty($selectbox_options[$selectbox_id])){
            $selected = selected( $selectbox_options[$selectbox_id][$blog], $blog, false);
            } 
            
            $blog_details = get_blog_details($blog);
	    echo '<option value="'.$blog.'" title="'.$blog_details->siteurl.'" '.$selected.'>'.$blog_details->blogname.'</option>';    

        }   
        
        echo '</select>';
        echo '<p>Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.</p>';
        echo '<div>';

    }

    // Checkbox pricing model
    public function pricing_checkbox_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert'.$currentScreen);
                 
        $pricing_array = array('CPC', 'CPM', 'CPP');

        echo '<div class="advert-multicheckbox">';
               
        foreach( $pricing_array as $type ){

            $checked = ( isset($checkbox_options[$checkbox_id.'-'.$type]) && intval($checkbox_options[$checkbox_id.'-'.$type]) === 1 ? $checked = 1 : $checked = 0 );        
            echo '<p><label for="'.$checkbox_id.'-'.$type.'">';
            echo '<input type="checkbox" id="'.$checkbox_id.'-'.$type.'" name="advert'.$currentScreen.'['.$checkbox_id.'-'.$type.']" value="1" '. checked( 1, $checked, false ) .' />'.$type.'</label></p>';
        }    

        echo '<div>';

    }




    public function advert_options_validate( $input ) {

        $sanitized_input = array();

        $nosanitize = array(
        'advert_display_advertise_here_text',
        'advert_display_text_to_reg_users',
        'advert_display_notice_blocked_text'
        );

        foreach ($input as $option => $value) {
           if( isset( $input[$option] ) && is_array( $input[$option] ) ){

	       foreach( $input[$option] as $additional_options ){
	           $sanitized_input[$option][$additional_options] = sanitize_text_field($additional_options);
	       }
        
           }
           elseif( isset( $input[$option] ) ) {

               if(in_array($option, $nosanitize)){
               $sanitized_input[$option] = wp_kses_post(htmlspecialchars($input[$option]));
               }
               else{
               $sanitized_input[$option] = sanitize_text_field($input[$option]);    
               }
           
           }
        }

        return apply_filters( 'advert_options_validate', $sanitized_input, $input );

    }	
	
	
    // Creating tables for all blogs in a WordPress Multisite installation
    private function advert_multisite_activate() {
        global $wpdb;
        global $advert_network_options;

        //setup options
	    $allow_sites_network_option = '';
	    if( array_key_exists('advert_network_allow_sites',$advert_network_options) ){
		    $allow_sites_network_option = $advert_network_options['advert_network_allow_sites'];
	    }

        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {

            switch_to_blog( $blog_id );
                advert_activate();
            restore_current_blog();

        }

    }


    // Creating table whenever a new blog is created
    private function advert_multisite_create_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( is_plugin_active_for_network( 'advert/advert.php' ) ) {
            switch_to_blog( $blog_id );
            advert_activate();
            restore_current_blog();
        }
    }



    // Deleting the table whenever a blog is deleted
    private function advert_multisite_delete_site( $tables ) {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'table_name';
        return $tables;
    }


    static function advert_get_main_blog_id() {

	    global $current_site;
	    global $wpdb;
	
	    return $wpdb->get_var ( $wpdb->prepare ( "SELECT `blog_id` FROM `$wpdb->blogs` WHERE `domain` = '%s' AND `path` = '%s' ORDER BY `blog_id` ASC LIMIT 1", $current_site->domain, $current_site->path ) );

    }


    static function advert_network_check_type(){

        global $advert_network_options;
        $current_site_id = get_current_blog_id();

        //setup options
	    $allow_sites_network_option = '';
	    if( array_key_exists('advert_network_allow_sites',$advert_network_options) ){
		    $allow_sites_network_option = $advert_network_options['advert_network_allow_sites'];
	    }

	    $disabled_sites_network_option = false;
	    if( array_key_exists('advert_network_disable_independent_sites',$advert_network_options) ){
	        $disabled_sites_network_option = true;
	    }

        $advert_network_control_disabled_independent_sites = 0;
	    if( array_key_exists('advert_network_control_disabled_independent_sites',$advert_network_options) ){
	        $advert_network_control_disabled_independent_sites = intval($advert_network_options['advert_network_control_disabled_independent_sites']);
	    }

        $advert_network_empty_independent_sites = 0;
	    if( array_key_exists('advert_network_empty_independent_sites',$advert_network_options) ){
	        $advert_network_empty_independent_sites = intval($advert_network_options['advert_network_empty_independent_sites']);
	    }

        
	    if( $allow_sites_network_option == 'Connected AdVert Network'  ){

		    return true;
		
	    }

	    if( $allow_sites_network_option == 'Independent AdVert Network' && $advert_network_control_disabled_independent_sites == 1 && $disabled_sites_network_option ){
				
		    if( in_array($current_site_id,$advert_network_options['advert_network_disable_independent_sites']) ){
			
			    return true;
			
		    }	    
		    else{
		    
                return false;
		    
		    }
		
	    }

	    if( $allow_sites_network_option == 'Independent AdVert Network' && $advert_network_control_disabled_independent_sites == 0 ){

            if( $disabled_sites_network_option ){
		        if( in_array($current_site_id,$advert_network_options['advert_network_disable_independent_sites']) ){

			        return 'disabled';
			
		        }
		        else{

                    if( $advert_network_empty_independent_sites == 1 ){

                        switch_to_blog($current_site_id);

                        //check all the campaigns for the current site
                        $args = array('post_type' => 'advert-campaign', 'post_status' => 'publish', 'posts_per_page' => -1);
                        $posts = get_posts( $args );
                        $activeCount = 0;
                        $today = strtotime(date('m/d/Y'));

                        foreach ( $posts as $post ){
                            $start = strtotime(get_post_meta($post->ID, 'campaign_start_date', true));
                            $stop  = strtotime(get_post_meta($post->ID, 'campaign_stop_date', true));
                            if ( $start <= $today && $stop >= $today || empty($start) && empty($stop) || $start <= $today && empty($stop) || empty($start) && $stop >= $today ){$activeCount = $activeCount + 1;}
                        }

                        wp_reset_postdata();

                        restore_current_blog();

                        if( intval($activeCount) <= 0 ){
                            $advert_network_options['advert_active_campaigns_none'] =  0;
                            return true;
                        }
                        else{
                            return false;
                        }

                    }
                    else{
                        return false;
                    }
		    
		        }
            }
		    else{

                if( $advert_network_empty_independent_sites == 1 ){

                    switch_to_blog($current_site_id);

                    //check all the campaigns for the current site
                    $args = array('post_type' => 'advert-campaign', 'post_status' => 'publish', 'posts_per_page' => -1);
                    $posts = get_posts( $args );
                    $activeCount = 0;
                    $today = strtotime(date('m/d/Y'));

                    foreach ( $posts as $post ){
                        $start = strtotime(get_post_meta($post->ID, 'campaign_start_date', true));
                        $stop  = strtotime(get_post_meta($post->ID, 'campaign_stop_date', true));
                        if ( $start <= $today && $stop >= $today || empty($start) && empty($stop) || $start <= $today && empty($stop) || empty($start) && $stop >= $today ){$activeCount = $activeCount + 1;}
                    }

                    wp_reset_postdata();

                    restore_current_blog();

                    if( intval($activeCount) <= 0 ){
                        $advert_network_options['advert_active_campaigns_none'] =  0;
                        return true;
                    }
                    else{
                        return false;
                    }

                }
                else{
                    return false;
                }
		    
		    }		    
		
	    }

        return false;

    }

}//end AdVert_For_Wordpress_Multisite