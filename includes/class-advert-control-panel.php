<?php

class AdVert_Control_Panel {

    private $options;

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'create_control_panel_submenu' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );

    }


    public function create_control_panel_submenu() {

        add_submenu_page('advert', __( 'Control Panel - General', 'ADVERT_TEXTDOMAIN' ), __( 'Control Panel', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-cp-general', array( $this, 'create_cp_general'));
        add_submenu_page('advert', __( 'Control Panel - Users', 'ADVERT_TEXTDOMAIN' ),  __( 'Control Panel', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-cp-users', array( $this, 'create_cp_users' ));
        add_submenu_page('advert', __( 'Control Panel - Ads', 'ADVERT_TEXTDOMAIN' ), __( 'Control Panel', 'ADVERT_TEXTDOMAIN' ), 'publish_adverts', 'advert-cp-ads', array( $this, 'create_cp_ads' ));
        add_submenu_page('advert', 'Post Data', 'Post Data', 'publish_adverts', 'postdata', 'advert_post_data');

    }

    public function create_cp_general() {

        // Set class property
        $this->options = get_option( 'advert_cp_options_general' );

        //display settings messages
        settings_errors();

        //get current screen and set option name
        global $currentScreen;
        $currentScreen = '_general';

        //get page - http or https
        $tab1 = esc_url(admin_url( 'admin.php?page=advert-cp-general' ));
        $tab2 = esc_url(admin_url( 'admin.php?page=advert-cp-users' ));
        $tab3 = esc_url(admin_url( 'admin.php?page=advert-cp-ads' ));

        ?>


        <div class="wrap advert-settings-wrap">

        <?php screen_icon(); ?>

        <div class="advert-page-heading-logo">a</div>

        <h1 class="advert-heading-tag"><?php _e( 'Control Panel', 'ADVERT_TEXTDOMAIN' ); ?></h1>
        <h2 class="dummy-h2"></h2>

        <div id="advert-control-panel" class="wrap">
        <div class="advert-tabbed">
        <a href="<?php echo $tab1; ?>"><h2 class="advert-tab-active"><?php _e( 'General', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab2; ?>"><h2><?php _e( 'Users', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab3; ?>"><h2><?php _e( 'Ads', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        </div>
        <div class="clear"></div>

        <form method="post" action="options.php">
        <div class="advert-inner-wrap">

        <?php

        settings_fields( 'advert_cp_options_general' );   
        do_settings_sections( 'advert-cp-general' );
        ?>
        </div>
        <?php submit_button(); ?>
        </form>

        <?php if(current_user_can('manage_options')) : ?>

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
        <?php _e('If you enjoy using AdVert, consider donating for continued plugin support and updates. If you are not able to make a donation right now, thats ok. You can <a href="https://wordpress.org/support/view/plugin-reviews/advert/" target="_blank">Rate AdVert</a>, enable the AdVert link above or tell people about AdVert for WordPress. AdVert will never offer a premium or pro version.', 'ADVERT_TEXTDOMAIN'); ?>
        </p>

        <?php endif; ?>

        <?php if(!current_user_can('manage_options')) : ?>

        <div class="clear"></div>

        <?php endif; ?>

        </div>
        </div>

        <?php
            

        add_action('admin_init', array($this, 'page_init'));
        
    }


    public function create_cp_users() {

        // Set class property
        $this->options = get_option( 'advert_cp_options_users' );

        //display settings messages
        settings_errors();

        //get current screen and set option name
        global $currentScreen;
        $currentScreen = '_users';

        //get page - http or https
        $tab1 = esc_url(admin_url( 'admin.php?page=advert-cp-general' ));
        $tab2 = esc_url(admin_url( 'admin.php?page=advert-cp-users' ));
        $tab3 = esc_url(admin_url( 'admin.php?page=advert-cp-ads' ));

        ?>


        <div class="wrap advert-settings-wrap">

        <?php screen_icon(); ?>

        <div class="advert-page-heading-logo">a</div>

        <h1 class="advert-heading-tag"><?php _e( 'Control Panel', 'ADVERT_TEXTDOMAIN' ); ?></h1>
        <h2 class="dummy-h2"></h2>

        <div id="advert-control-panel" class="wrap">
        <div class="advert-tabbed">
        <a href="<?php echo $tab1; ?>"><h2><?php _e( 'General', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab2; ?>"><h2 class="advert-tab-active"><?php _e( 'Users', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab3; ?>"><h2><?php _e( 'Ads', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        </div>
        <div class="clear"></div>

        <form method="post" action="options.php">
        <div class="advert-inner-wrap">

        <?php

        settings_fields( 'advert_cp_options_users' );   
        do_settings_sections( 'advert-cp-users' );
        ?>
        </div>
        <?php submit_button(); ?>
        </form>

        <?php if(current_user_can('manage_options')) : ?>

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
        <?php _e('If you enjoy using AdVert, consider donating for continued plugin support and updates. If you are not able to make a donation right now, thats ok. You can <a href="https://wordpress.org/support/view/plugin-reviews/advert/" target="_blank">Rate AdVert</a>, enable the AdVert link on the General Tab or tell people about AdVert for WordPress. AdVert will never offer a premium or pro version.', 'ADVERT_TEXTDOMAIN'); ?>
        </p>

        <?php endif; ?>

        <?php if(!current_user_can('manage_options')) : ?>

        <div class="clear"></div>

        <?php endif; ?>

        </div>
        </div>

        <?php
            
    }



    public function create_cp_ads() {

        // Set class property
        $this->options = get_option( 'advert_cp_options_ads' );

        //display settings messages
        settings_errors();

        //get current screen and set option name
        global $currentScreen;
        $currentScreen = '_ads';

        //get page - http or https
        $tab1 = esc_url(admin_url( 'admin.php?page=advert-cp-general' ));
        $tab2 = esc_url(admin_url( 'admin.php?page=advert-cp-users' ));
        $tab3 = esc_url(admin_url( 'admin.php?page=advert-cp-ads' ));

        ?>


        <div class="wrap advert-settings-wrap">

        <?php screen_icon(); ?>

        <div class="advert-page-heading-logo">a</div>

        <h1 class="advert-heading-tag"><?php _e( 'Control Panel', 'ADVERT_TEXTDOMAIN' ); ?></h1>
        <h2 class="dummy-h2"></h2>

        <div id="advert-control-panel" class="wrap">
        <div class="advert-tabbed">
        <a href="<?php echo $tab1; ?>"><h2><?php _e( 'General', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab2; ?>"><h2><?php _e( 'Users', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab3; ?>"><h2 class="advert-tab-active"><?php _e( 'Ads', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        </div>
        <div class="clear"></div>

        <form method="post" action="options.php">
        <div class="advert-inner-wrap">

        <?php

        settings_fields( 'advert_cp_options_ads' );   
        do_settings_sections( 'advert-cp-ads' );
        ?>
        </div>
        <?php submit_button(); ?>
        </form>

        <?php if(current_user_can('manage_options')) : ?>

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
        <?php _e('If you enjoy using AdVert, consider donating for continued plugin support and updates. If you are not able to make a donation right now, thats ok. You can <a href="https://wordpress.org/support/view/plugin-reviews/advert/" target="_blank">Rate AdVert</a>, enable the AdVert link on the General Tab or tell people about AdVert for WordPress. AdVert will never offer a premium or pro version.', 'ADVERT_TEXTDOMAIN'); ?>
        </p>

        <?php endif; ?>

        <?php if(!current_user_can('manage_options')) : ?>

        <div class="clear"></div>

        <?php endif; ?>

        </div>
        </div>

        <?php
            
    }


    public function page_init() {

        global $advert_options;

        register_setting('advert_cp_options_general', 'advert_cp_options_general', array( $this, 'advert_cp_options_validate' ));
        register_setting('advert_cp_options_users', 'advert_cp_options_users', array( $this, 'advert_cp_options_validate' ));
        register_setting('advert_cp_options_ads', 'advert_cp_options_ads', array( $this, 'advert_cp_options_validate' ));

        //general
        add_settings_section(
        'advert_options_cp_general_s1', // ID
        __( 'General Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_general_s1' ), // Callback
        'advert-cp-general' // Page
        );

        add_settings_field(
        'advert_pending_emails', // ID
        __( 'Send Email Notifications For Pending Items', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s1', // Section
        $args = array ('label_for' => 'advert_pending_emails')
        );

        add_settings_field(
        'advert_published_emails', // ID
        __( 'Send Email Notifications For Published Items', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s1', // Section
        $args = array ('label_for' => 'advert_published_emails')
        );

        //check if user can manage options
        if( current_user_can('manage_options') ){


        add_settings_section(
        'advert_options_cp_general_s2', // ID
        __( 'Payments', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_general_s2' ), // Callback
        'advert-cp-general' // Page
        );


        $advert_payment_plugins = get_option('advert_payment_plugins');
        if(is_array($advert_payment_plugins)){

            $selectOptions = array ('label_for' => 'advert_default_payments', 'option1' => 'none');

            $counter = 2;
            foreach($advert_payment_plugins as $plugin){              
                $selectOptions['option'.$counter] = $plugin;
                $counter = $counter + 1;
            }

        }
        else{
            $selectOptions = array ('label_for' => 'advert_default_payments', 'option1' => 'none');
        }

        add_settings_field(
        'advert_default_payments', // ID
        __( 'Payment Method', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'select_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions
        );

        $payment_option = '';
        if( array_key_exists('advert_default_payments',$advert_options) ){
            $payment_option = $advert_options['advert_default_payments'];
        }

        //if payment plugin
        if($payment_option != 'none'  && $payment_option != ''){
            do_action('advert_add_payment_settings_' . $payment_option);
        }//end check if payment plugin


        add_settings_section(
        'advert_options_cp_general_s3', // ID
        __( 'Advanced Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_general_s3' ), // Callback
        'advert-cp-general' // Page
        );

        add_settings_field(
        'advert_redo_standard_locations', // ID
        __( 'Restore Standard Locations', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'restore_standard_locations_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s3', // Section
        $args = array ('label_for' => 'advert_redo_standard_locations', 'text' => __( 'This feature will attempt to restore standard locations. These are added when you install AdVert.', 'ADVERT_TEXTDOMAIN' ) )
        );

        add_settings_field(
        'advert_lock_rates', // ID
        __( 'Turn off Locked Rates', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s3', // Section
        $args = array ('label_for' => 'advert_lock_rates', 'warning' => __( 'Warning: This feature turns off locked in rates. Locations prices can be changed without notice to the advertiser. Meaning: this could make some advertisers livid.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_turn_off', // ID
        __( 'Turn off AdVert', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s3', // Section
        $args = array ('label_for' => 'advert_turn_off', 'warning' => __( 'Warning: This feature turns off AdVert, i.e. no ads will be displayed.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_delete_all', // ID
        __( 'Empty AdVert Database', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'empty_advert_db_checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s3', // Section
        $args = array ('label_for' => 'advert_delete_all')
        );

        //advert plugin stuff
        add_settings_section(
        'advert_options_cp_general_s4', // ID
        __( 'AdVert for WordPress', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_general_s4' ), // Callback
        'advert-cp-general' // Page
        );

        add_settings_field(
        'advert_about_link', // ID
        __( 'Display about AdVert link below feedback (if enabled)', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s4', // Section
        $args = array ('label_for' => 'advert_about_link')
        );

        }//end manage options



        //users
        add_settings_section(
        'advert_options_cp_users_s1', // ID
        __( 'User Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_users_s1' ), // Callback
        'advert-cp-users' // Page
        );  

        add_settings_field(
        'advert_register_users', // ID
        __( 'Allow Users to register to become an Advertiser', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s1', // Section
        $args = array ('label_for' => 'advert_register_users')
        );

        add_settings_field(
        'advert_display_text_to_reg_users', // ID
        __( 'Text that is displayed to the user who wants to start advertising', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'textarea_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s1', // Section
        $args = array ('label_for' => 'advert_display_text_to_reg_users')
        );

        add_settings_field(
        'advert_allow_analysis_users', // ID
        __( 'Allow Users to view historical data Analysis - Only the users data is shown', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s1', // Section
        $args = array ('label_for' => 'advert_allow_analysis_users')
        );

        add_settings_field(
        'advert_allow_pricing_model_users', // ID
        __( 'Allow Users to select these pricing models', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'pricing_checkbox_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s1', // Section
        $args = array ('label_for' => 'advert_allow_pricing_model_users')
        );

        if( current_user_can('manage_options') ){

        add_settings_section(
        'advert_options_cp_users_s2', // ID
        __( 'AdVert Manager Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_users_s2' ), // Callback
        'advert-cp-users' // Page
        );  

        add_settings_field(
        'advert_advertmgr_adcredits', // ID
        __( 'Add/Remove AdCredits', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s2', // Section
        $args = array ('label_for' => 'advert_advertmgr_adcredits')
        );

        add_settings_field(
        'advert_advertmgr_add_location', // ID
        __( 'Add a new location', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-users', // Page
        'advert_options_cp_users_s2', // Section
        $args = array ('label_for' => 'advert_advertmgr_add_location')
        );

        }//end manage options



        //ads
        add_settings_section(
        'advert_options_cp_ads_s1', // ID
        __( 'Ad Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_ads_s1' ), // Callback
        'advert-cp-ads' // Page
        );

        add_settings_field(
        'advert_feedback', // ID
        __( 'Allow Ad Feedback', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_feedback', 'text' => __('Gives your visitors a way to provide feedback about ads being displayed.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_feedback_hide_negative', // ID
        __( 'Hide Ads that have negative user feedback', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_feedback_hide_negative', 'text' => __( 'If an ad received negative feedback, hide that particular ad from that user who gave negative feedback.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_identify_ad', // ID
        __( 'Display Advertisement above the Banner', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_identify_ad', 'text' => __( 'Displays the word Advertisement above the ad, which helps users identify ads vs content. This helps prevent useless clicks for the advertiser.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_spammed_clicks', // ID
        __( 'Prevent spammed clicks', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_spammed_clicks')
        );

        add_settings_field(
        'advert_display_advertise_here', // ID
        __( 'Display Notice for empty ad spaces', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_display_advertise_here')
        );

        add_settings_field(
        'advert_display_advertise_here_text', // ID
        __( 'Notice for empty ad spaces', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textarea_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_display_advertise_here_text')
        );

        add_settings_field(
        'advert_allow_editors_turn_off_ads', // ID
        __( 'Allow Editors to turn off advertisements by post/page', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_allow_editors_turn_off_ads', 'warning' => __( 'Warning: Editors will be able to disable ads for the page or post.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_turn_off_ads_post_type', // ID
        __( 'Turn off ads by post type', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_post_type_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s1', // Section
        $args = array ('label_for' => 'advert_turn_off_ads_post_type')
        );

        //add_settings_section(
        //'advert_options_cp_ads_s2', // ID
        //__( 'Image', 'ADVERT_TEXTDOMAIN' ), // Title
        //array( $this, 'print_section_info_ads_s2' ), // Callback
        //'advert-cp-ads' // Page
        //);

        add_settings_section(
        'advert_options_cp_ads_s3', // ID
        __( 'Video', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_ads_s3' ), // Callback
        'advert-cp-ads' // Page
        );

        add_settings_field(
        'advert_hide_videos', // ID
        __( 'Hide Videos after played', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s3', // Section
        $args = array ('label_for' => 'advert_hide_videos')
        );

        //add_settings_section(
        //'advert_options_cp_ads_s4', // ID
        //__( 'Text', 'ADVERT_TEXTDOMAIN' ), // Title
        //array( $this, 'print_section_info_ads_s4' ), // Callback
        //'advert-cp-ads' // Page
        //);

        add_settings_section(
        'advert_options_cp_ads_s5', // ID
        __( 'Blocking Ads', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_ads_s5' ), // Callback
        'advert-cp-ads' // Page
        );

        add_settings_field(
        'advert_display_notice_blocked', // ID
        __( 'Display Notice If Visitor Is Blocking Ads', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s5', // Section
        $args = array ('label_for' => 'advert_display_notice_blocked')
        );

        add_settings_field(
        'advert_display_notice_blocked_text', // ID
        __( 'Message To Display To Visitor Blocking Ads', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textarea_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s5', // Section
        $args = array ('label_for' => 'advert_display_notice_blocked_text')
        );


        //check if user can manage options
        if( current_user_can('manage_options') ){

        add_settings_section(
        'advert_options_cp_ads_s6', // ID
        __( 'Advanced Ad Settings', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'print_section_info_ads_s6' ), // Callback
        'advert-cp-ads' // Page
        );

        add_settings_field(
        'advert_disable_member_ads', // ID
        __( 'Disable Ad Tracking for logged in users', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s6', // Section
        $args = array ('label_for' => 'advert_disable_member_ads', 'warning' => __( 'Warning: Clicks, Impressions and Feedback will not be added to the database for logged in users.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_log_ads', // ID
        __( 'Disable Ad Tracking <br />(clicks, impressions etc.)', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s6', // Section
        $args = array ('label_for' => 'advert_log_ads', 'warning' => __( 'Warning: Clicks, Impressions and Feedback will be disabled and not be added to the database.', 'ADVERT_TEXTDOMAIN' ))
        );

        add_settings_field(
        'advert_decode_key', // ID
        __( 'AdVert Encryption Key', 'ADVERT_TEXTDOMAIN' ), // Title
        array( $this, 'textbox_callback' ), // Callback
        'advert-cp-ads', // Page
        'advert_options_cp_ads_s6', // Section
        $args = array ('label_for' => 'advert_decode_key')
        );

        }

    }// End page_init


    //infobox
    public function print_section_info_general_s1() {
        echo '<span>' . __( 'As the title states, these are general settings for AdVert', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_general_s2() {
        echo '<span>' . __( 'Select a payment option for AdVert users', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_general_s3() {
        echo '<span>' . __( 'Most of these settings are self-explanitory, but when in doubt click the help tab above for more information - Only site admins can see this section', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_general_s4() {
        echo '<span>' . __( 'Optional stuff to show your support of AdVert for WordPress', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_users_s1() {
        echo '<span>' . __( 'Change settings for users', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_users_s2() {
        echo '<span>' . __( 'Change settings for AdVert Managers', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s1() {
        echo '<span>' . __( 'General Ad Settings', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s2() {
        echo '<span>' . __( 'Banners that are images', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s3() {
        echo '<span>' . __( 'Banners that are videos', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s4() {
        echo '<span>' . __( 'Banners that are text', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s5() {
        echo '<span>' . __( 'Blocking Ads', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }

    public function print_section_info_ads_s6() {
        echo '<span>' . __( 'Ad Flow', 'ADVERT_TEXTDOMAIN' ) . '</span>';
    }


    // Textboxes
    public function textbox_callback(array $args) {

        global $currentScreen;
        $textbox_id = $args['label_for'];
        $type = array_key_exists('type', $args) ? $args['type'] : 'text';
        $textbox_options = get_option('advert_cp_options'.$currentScreen);
        $maxChar = $textbox_id === 'advert_decode_key' ? $maxChar = 'maxlength="16"' : '';
        printf(
        '<input type="'.$type.'" id="'.$textbox_id.'" name="advert_cp_options'.$currentScreen.'['.$textbox_id.']" '.$maxChar.' value="%s" />',
        isset( $textbox_options[$textbox_id] ) ? esc_attr( $textbox_options[$textbox_id] ) : ''
        );            

    }

    // Textarea
    public function textarea_callback(array $args) {

        global $currentScreen;
        $textarea_id = $args['label_for'];
        $text = ( array_key_exists('text', $args) ? '<br /><p>' . $args['text'] . '</p>' : '' );
        $textarea_options = get_option('advert_cp_options'.$currentScreen);
        printf(
        '<textarea rows="4" cols="50" id="'.$textarea_id.'" name="advert_cp_options'.$currentScreen.'['.$textarea_id.']">%s</textarea>'.$text,
        isset( $textarea_options[$textarea_id] ) ? esc_attr( $textarea_options[$textarea_id] ) : ''
        );

    }

    // Selection
    public function select_callback(array $selectOptions) {

        global $currentScreen;
        $select_id = $selectOptions['label_for'];
        $text = ( array_key_exists('text', $selectOptions) ? '<span class="advert-span-break"></span><span class="advert-span-info">' . $args['text'] . '</span>' : '' );
        $select_options = get_option('advert_cp_options'.$currentScreen);
        $howmany = sizeof($selectOptions);
        $count = 1;
        $selected = '';
        $html = '<select id="'.$select_id.'" name="advert_cp_options'.$currentScreen.'['.$select_id.']">';
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
        $checkbox_options = get_option('advert_cp_options'.$currentScreen);
        $checked = ( isset($checkbox_options[$checkbox_id]) && intval($checkbox_options[$checkbox_id]) === 1 ? $checked = 1 : $checked = 0 );
        $text = ( array_key_exists('text', $args) ? '<span class="advert-span-break"></span><span class="advert-span-info">' . $args['text'] . '</span>' : '' );
        $warning = ( array_key_exists('warning', $args) ? '<span class="advert-span-break"></span><span class="advert-cp-warning-msg advert-span-info">' . $args['warning'] . '</span>' : '' );
        $warningClass = ( !empty($warning) ? $warningClass = 'class="hide-if-no-js advert-cp-warning"' : '' );
        $html = '<input type="checkbox" id="'.$checkbox_id.'" ' . $warningClass . ' name="advert_cp_options'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ) .' />';
        echo $html.$text.$warning;

    }

    // Radio
    public function radio_callback(array $radioOptions) {

        global $currentScreen;
        $radio_id = $radioOptions['label_for'];
        $text = ( array_key_exists('text', $args) ? '<br /><p>' . $args['text'] . '</p>' : '' );
        $radio_options = get_option('advert_cp_options'.$currentScreen);
        $howmany = sizeof($radioOptions);
        $count = 1;
        $html = '';
        while ($count < $howmany) {
        $html .= '<label for="'.$radio_id.$count.'">'.$radioOptions['radio'.$count].'<input type="radio" id="'.$radio_id.$count.'" name="advert_cp_options'.$currentScreen.'['.$radio_id.']" value="'.$count.'" '. checked( $count, $radio_options[$radio_id], false ).' style="margin-left:5px;" /></label><br>';
        $count = $count + 1;
        }
        echo $html.$text;

    }


    public function restore_standard_locations_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert_cp_options'.$currentScreen);
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
        $html = '<input type="checkbox" id="'.$checkbox_id.'" ' . $warningClass . ' name="advert_cp_options'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ).' />';
        echo $html.$text.$warning;
    }


    //adds an option for empty callback which is useful for links and whatnot
    public function blank_callback(){
        
        return;

    }


    // Checkbox specific to empty advert database
    public function empty_advert_db_checkbox_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert_cp_options'.$currentScreen);
        if(isset($checkbox_options[$checkbox_id]) && $checkbox_options[$checkbox_id] === '1' && current_user_can('manage_options')){
        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_logged';
        $delete = $wpdb->query( "TRUNCATE TABLE $table_name " );
        $checked = 0;
        }
        else{$checked = 0;}
        $warning = '<span class="advert-span-break"></span><span class="advert-cp-warning-msg advert-span-info" class="advert-language-js">' . __( 'Warning: Selecting this option will remove all the data from the AdVert database table (advert_logged), payment information will not be affected. Its recomended that you backup your database before continuing.', 'ADVERT_TEXTDOMAIN') . '</span>';
        $html = '<input type="checkbox" id="'.$checkbox_id.'" class="hide-if-no-js advert-cp-warning" name="advert_cp_options'.$currentScreen.'['.$checkbox_id.']" value="1" '. checked( 1, $checked, false ).' />';
        echo $html.$warning;

    }


    // Checkbox post type
    public function checkbox_post_type_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert_cp_options'.$currentScreen);
                 
        $post_args = array(
           'public'   => true
        );

        $post_types = get_post_types( $post_args ); 
        
        echo '<div class="advert-multicheckbox">';

        foreach( $post_types as $type){

            $checked = ( isset($checkbox_options[$checkbox_id.'-'.$type]) && intval($checkbox_options[$checkbox_id.'-'.$type]) === 1 ? $checked = 1 : $checked = 0 );        
            echo '<p><label for="'.$checkbox_id.'-'.$type.'">';
            echo '<input type="checkbox" id="'.$checkbox_id.'-'.$type.'" name="advert_cp_options'.$currentScreen.'['.$checkbox_id.'-'.$type.']" value="1" '. checked( 1, $checked, false ) .' />'.$type.'</label></p>';
            
        }    

        echo '<div>';

    }

    // Checkbox pricing model
    public function pricing_checkbox_callback(array $args) {

        global $currentScreen;
        $checkbox_id = $args['label_for'];
        $checkbox_options = get_option('advert_cp_options'.$currentScreen);
                 
        $pricing_array = array('CPC', 'CPM', 'CPP');

        echo '<div class="advert-multicheckbox">';
               
        foreach( $pricing_array as $type ){

            $checked = ( isset($checkbox_options[$checkbox_id.'-'.$type]) && intval($checkbox_options[$checkbox_id.'-'.$type]) === 1 ? $checked = 1 : $checked = 0 );        
            echo '<p><label for="'.$checkbox_id.'-'.$type.'">';
            echo '<input type="checkbox" id="'.$checkbox_id.'-'.$type.'" name="advert_cp_options'.$currentScreen.'['.$checkbox_id.'-'.$type.']" value="1" '. checked( 1, $checked, false ) .' />'.$type.'</label></p>';
        }    

        echo '<div>';

    }

    public function advert_cp_options_validate( $input ) {

        $sanitized_input = array();

        $nosanitize = array(
        'advert_display_advertise_here_text',
        'advert_display_text_to_reg_users',
        'advert_display_notice_blocked_text'
        );

        $plugnosanitize = has_filter('advert_no_sanitize') ? apply_filters('advert_no_sanitize', true) : '';

        if(!is_array($plugnosanitize)){
            $plugnosanitize = [];
        }

        foreach ($input as $option => $value) {
           if( isset( $input[$option] ) ) {

               if(in_array($option, $nosanitize) || in_array($option, $plugnosanitize)){
               $sanitized_input[$option] = wp_kses_post(htmlspecialchars($input[$option]));
               }
               else{
               $sanitized_input[$option] = sanitize_text_field($input[$option]);    
               }
           
           }
        }

        return apply_filters( 'advert_cp_options_validate', $sanitized_input, $input );

    } 

}// End Class