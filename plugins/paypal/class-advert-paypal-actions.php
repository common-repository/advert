<?php

class AdVert_PayPal_Actions {


    public function __construct() {

        //paypal IPN stuff
        add_action('init', array($this, 'advert_add_endpoint'), 0);
        add_action('parse_request', array($this, 'advert_handle_endpoint_requests'), 0);
        add_action('advert_ipn_handler', array($this, 'advert_ipn_handler'));
        add_action('init', array($this, 'advert_create_ipn_log_dir'), 0);

        if( is_admin() ){
            //add an action to get the default paypal button(s) information
            add_action('advert_payment_type', array($this, 'advert_paypal_button_info'));     

            add_action('advert_lightbox', array($this, 'advert_lightbox_paypal_info'));

            //create 
            add_filter('advert_paypal_button', array($this, 'advert_create_paypal_button'), 10, 8);
        }

    }
  

    public function advert_process_adcredits($posted){

        // Parse data from IPN $posted[] array
        $item_number          = isset($posted['item_number']) ? $posted['item_number'] : '';
        $first_name           = isset($posted['first_name']) ? $posted['first_name'] : '';
        $last_name            = isset($posted['last_name']) ? $posted['last_name'] : '';
        $mc_gross             = isset($posted['mc_gross']) ? $posted['mc_gross'] : '';
        $recurring_payment_id = isset($posted['recurring_payment_id']) ? $posted['recurring_payment_id'] : '';
        $payer_email          = isset($posted['payer_email']) ? $posted['payer_email'] : '';
        $txn_id               = isset($posted['txn_id']) ? $posted['txn_id'] : '';

        //preg_match('/aid(.*?)eaid/', $item_number, $advertiser_id);
 
        $transaction = apply_filters('add_adcredits', $item_number, $mc_gross, '', 'PayPal', $txn_id);    

        if($transaction)
        do_action('calc_adcredits', $item_number);

    }


    public function advert_process_remove_adcredits($posted){

        // Parse data from IPN $posted[] array
        $item_number          = isset($posted['item_number']) ? $posted['item_number'] : '';
        $first_name           = isset($posted['first_name']) ? $posted['first_name'] : '';
        $last_name            = isset($posted['last_name']) ? $posted['last_name'] : '';
        $mc_gross             = isset($posted['mc_gross']) ? $posted['mc_gross'] : '';
        $recurring_payment_id = isset($posted['recurring_payment_id']) ? $posted['recurring_payment_id'] : '';
        $payer_email          = isset($posted['payer_email']) ? $posted['payer_email'] : '';
        $txn_id               = isset($posted['txn_id']) ? $posted['txn_id'] : '';

        $transaction = apply_filters('remove_adcredits', $item_number, $mc_gross, '', 'PayPal', $txn_id);   

        if($transaction)
        do_action('calc_adcredits', $item_number);

    }


    public function advert_lightbox_paypal_info(){


        //who are we working with
        $user_id = intval(get_current_user_id());
        $post_id = intval(apply_filters('get_advertiser_id', $user_id));

        if (isset($_GET['action']) && isset($_GET['_wpnonce'])){

            if ( wp_verify_nonce( $_GET['_wpnonce'], 'advert-adcredit-'.$post_id ) && current_user_can('edit_adverts') ){

                echo '<div id="advert-lightbox">';
                echo '<div id="advert-lightbox-overlay" class="avbg60">';
                echo '<div id="advert-lightbox-center-wrap">';
                echo '<div id="advert-lightbox-wrap">';
                echo '<div class="advert-lightbox-close">X</div>';

                if($_GET['action'] === 'return'){
                    echo '<p>' . __( 'Thank you for your payment.<br /><br />Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at <a href="https://www.paypal.com">PayPal</a> to view details of this transaction.<br /><br />Your AdCredits will be added to your account shortly.', 'ADVERT_TEXTDOMAIN' ) . '</a></p>';                
                }

                if($_GET['action'] === 'cancel-return'){
                    echo '<p>' . __( 'Your payment has been cancelled.', 'ADVERT_TEXTDOMAIN' ) . '</a></p>';
                }

                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

            }

        }

    }


    public function advert_paypal_button_info(){

        global $advert_options;

        //advert payment plugins
        $name = '';
        if(array_key_exists('advert_paypal_api_user_name',$advert_options)){
            $name = $advert_options['advert_paypal_api_user_name'];
        }

        $pw = '';
        if(array_key_exists('advert_paypal_api_pw',$advert_options)){
            $pw = $advert_options['advert_paypal_api_pw'];
        }

        $sig = '';
        if(array_key_exists('advert_paypal_api_signature',$advert_options)){
            $sig = $advert_options['advert_paypal_api_signature'];
        }

        $default_pricing = '5,10,15';
        if(array_key_exists('advert_default_paypal_pricing',$advert_options)){
            $default_pricing = $advert_options['advert_default_paypal_pricing'];
        }

        //default currency
        $currency = 'USD';
        if(array_key_exists('advert_default_paypal_currency',$advert_options)){
            $currency = $advert_options['advert_default_paypal_currency'];
        }

        if(empty($name) || empty($pw) || empty($sig))
            return;

        //who are we working with
        $user_id = intval(get_current_user_id());
        $post_id = intval(apply_filters('get_advertiser_id', $user_id));

        echo '<br /><hr><br />';

        _e( '<p>Select the amount of AdCredits you want to add then click Buy Now</p>', 'ADVERT_TEXTDOMAIN');         

        echo '<p>';
        echo '<span class="advert-open-rates-data hide-if-no-js">&nbsp;<a href="#">' . __('Current Rates', 'ADVERT_TEXTDOMAIN') . '</a></span>';
        echo '</p>';

        do_action('advert_current_rates');

        $itemNumber   = $post_id;
        $itemName     = get_bloginfo() . ' - Advertising by AdVert';
        $itemCurrency = $currency;

        $default_pricing = explode(',', $default_pricing);
        $nonce = wp_create_nonce( 'advert-adcredit-'.$post_id );
        
        echo '<div class="advert-payment-buttons">';

        foreach($default_pricing as $itemAmount){
            
            //attempt to create a button using NVP api
            $returnButton = apply_filters('advert_paypal_button', $name,$pw,$sig,$itemNumber,$itemName,$itemAmount,$itemCurrency,$nonce);

            //check if the button was successful
            if($returnButton){
                echo '<div style="display:inline-block;padding:40px;"><p style="font-weight:bold;font-size:28px;margin:0px;">' . $itemAmount . '</p><p>' . __('AdCredits', 'ADVERT_TEXTDOMAIN') . '</p>' . $returnButton . '</div>';
            }

        }

        //create a variable amount
        $itemAmount   = '0.00';

        //attempt to create a button using NVP api
        $returnButton = apply_filters('advert_paypal_button', $name,$pw,$sig,$itemNumber,$itemName,$itemAmount,$itemCurrency,$nonce);

        //check if the button was successful
        if($returnButton){
            echo '<div style="display:inline-block;padding:40px;"><p style="font-weight:bold;font-size:28px;margin:0px;">' . __('Other', 'ADVERT_TEXTDOMAIN') . '</p><p>' . __('Amount', 'ADVERT_TEXTDOMAIN') . '</p>' . $returnButton . '</div>';
        }

        echo '</div>';

    }


    /**
     * Add endpoint
     *
     * Domain + ?AdVert_PayPal_IPN
     * Full url for ipn/endpoint: yourdomain/?AdVert_PayPal_IPN&action=advert_ipn_handler
     *
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function advert_add_endpoint() {

        // Endpoint for PayPal IPN gateway
        add_rewrite_endpoint('AdVert_PayPal_IPN', EP_ALL);

    }


    /**
     * Endpoint requests
     *
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function advert_handle_endpoint_requests() {

        global $wp;

        if (isset($_GET['action']) && $_GET['action'] === 'advert_ipn_handler') {

            //Prevent output
            ob_start();

            //call IPN function
            do_action('advert_ipn_handler');

            //Done, clear buffer and exit
            ob_end_clean();
            die('0');

        }

    }


    public function advert_ipn_handler() {

        /**
         * The class responsible for defining all actions related to paypal ipn listener 
         */
        require_once(ADVERT_PLUGIN_DIR . 'plugins/paypal/class-advert-paypal-ipn.php');
        //require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-paypal-ipn-for-wordpress-paypal-helper.php';
        $AdVert_Paypal_Ipn_Checker_Object = new AdVert_Paypal_Ipn_Checker();

        /**
         * The check_ipn_request function check and validation for ipn response
         */
        if($AdVert_Paypal_Ipn_Checker_Object->advert_check_ipn_request()){

            add_action('advert_payment_status_completed', array($this, 'advert_process_adcredits'), 10, 1);
            add_action('advert_payment_status_canceled_reversal', array($this, 'advert_process_adcredits'), 10, 1);
            //add_action('advert_adaptive_status_completed', array($this, 'advert_process_adcredits'), 10, 1);
        
            add_action('advert_payment_status_refunded', array($this, 'advert_process_remove_adcredits'), 10, 1);
            add_action('advert_payment_status_reversed', array($this, 'advert_process_remove_adcredits'), 10, 1);
            add_action('advert_masspay_payment_status_refunded', array($this, 'advert_process_remove_adcredits'), 10, 1);
            //add_action('advert_txn_type_adjustment', array($this, 'advert_process_remove_adcredits'), 10, 1);

            $AdVert_Paypal_Ipn_Checker_Object->advert_successful_request($IPN_status = true);

        }
        else{
            $AdVert_Paypal_Ipn_Checker_Object->advert_successful_request($IPN_status = false);
        }
    }


    /**
     * Create folder and file if not exist
     *
     */
    public function advert_create_ipn_log_dir() {
        // Install files and folders for uploading files and prevent hotlinking

        $files = array(
            array(
                'base' => ADVERT_PAYPAL_IPN_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => ADVERT_PAYPAL_IPN_LOG_DIR,
                'file' => 'index.html',
                'content' => ''
            )
        );

        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    public function advert_create_paypal_button($name,$pw,$sig,$itemNumber,$itemName,$itemAmount,$itemCurrency,$nonce){
        
        $returnTrue  = esc_url(admin_url( 'admin.php?page=advert-user' )).'&action=return&_wpnonce='.$nonce;
        $returnFalse = esc_url(admin_url( 'admin.php?page=advert-user' )).'&action=cancel-return&_wpnonce='.$nonce;
        $ipnURL      = site_url('?AdVert_PayPal_IPN&action=advert_ipn_handler');

        $sendPayData = array(
            "METHOD" => "BMCreateButton",
            "VERSION" => "124",
            "USER" => $name,
            "PWD" => $pw,
            "SIGNATURE" => $sig,
            "BUTTONCODE" => "ENCRYPTED",
            "BUTTONTYPE" => "BUYNOW",
            "BUTTONSUBTYPE" => "SERVICES",
            "BUTTONIMAGE" => "REG",
            "BUYNOWTEXT" => "BUYNOW",
            "L_BUTTONVAR1" => "item_number=$itemNumber",
            "L_BUTTONVAR2" => "item_name=$itemName",
            "L_BUTTONVAR3" => "amount=$itemAmount",
            "L_BUTTONVAR4" => "currency_code=$itemCurrency",
            "L_BUTTONVAR5" => "no_shipping=1",
            "L_BUTTONVAR6" => "no_note=1",
            "L_BUTTONVAR7" => "notify_url=$ipnURL",
            "L_BUTTONVAR8" => "cancel_return=$returnFalse",
            "L_BUTTONVAR9" => "return=$returnTrue"
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, 'https://api-3t.paypal.com/nvp?'.http_build_query($sendPayData));
        $nvpPayReturn = curl_exec($curl);
        curl_close($curl);

        parse_str($nvpPayReturn, $nvpPayVariables);

        if( $nvpPayVariables['ACK'] === 'Success' ){
            return $nvpPayVariables['WEBSITECODE'];               
        }
        else{
            return false;
        }

    

    }



}// End AdVert_PayPal_Actions Class


class AdVert_PayPal_Settings extends AdVert_Control_Panel {

    public function __construct() {

        //add action for advert settings page
        add_action('advert_add_payment_settings_paypal', array($this, 'advert_add_payment_settings'));

        //array for verification
        add_filter('advert_no_sanitize', array( $this, 'advert_no_sanitize' ), 1 );

    }

    public function advert_no_sanitize(){
        
        $plugnosanitize = array(
        'advert_paypal_email_address'  
        );

        return $plugnosanitize;

    }

    public function advert_add_payment_settings(){
     
        add_settings_field(
        'advert_paypal_api_user_name', // ID
        __( 'PayPal API User Name', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_paypal_api_user_name', 'type' => 'text')
        );

        add_settings_field(
        'advert_paypal_api_pw', // ID
        __( 'PayPal API Password', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_paypal_api_pw', 'type' => 'password')
        );

        add_settings_field(
        'advert_paypal_api_signature', // ID
        __( 'PayPal API Signature', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_paypal_api_signature', 'type' => 'text')
        );

        add_settings_field(
        'advert_paypal_api_howto', // ID
        __( '<a href="https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-classic-api-credentials" target="_blank">Get your API information</a>', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'blank_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2' // Section
        );       

        add_settings_field(
        'advert_default_paypal_currency', // ID
        __( 'Currency Type', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'select_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array (
            'label_for' => 'advert_default_paypal_currency', 
            'option1' => 'AUD', 
            'option2' => 'CAD', 
            'option3' => 'CZK', 
            'option4' => 'DKK', 
            'option5' => 'EUR', 
            'option6' => 'HKD', 
            'option7' => 'ILS', 
            'option8' => 'MXN', 
            'option9' => 'NOK', 
            'option10' => 'NZD', 
            'option11' => 'PHP', 
            'option12' => 'PLN', 
            'option13' => 'GBP', 
            'option14' => 'SGD', 
            'option15' => 'SEK', 
            'option16' => 'CHF', 
            'option17' => 'THB', 
            'option18' => 'USD'
            )
        );

        add_settings_field(
        'advert_default_paypal_pricing', // ID
        __( 'Default AdCredit Amount', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'select_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array (
            'label_for' => 'advert_default_paypal_pricing', 
            'option1' => '5,10,20', 
            'option2' => '10,20,40', 
            'option3' => '15,30,60', 
            'option4' => '20,40,80', 
            'option5' => '25,50,100', 
            'option6' => '30,60,120', 
            'option7' => '60,120,240', 
            'option8' => '120,240,480', 
            'option9' => '250,500,1000', 
            'option10' => '500,1000,2000', 
            'option11' => '1000,2000,4000', 
            'option12' => '5000,10000,20000'
            )
        );

        add_settings_field(
        'advert_paypal_ipn', // ID
        __( 'PayPal IPN URL', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'ipn_textbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_paypal_ipn')
        );

        add_settings_field(
        'advert_paypal_ipn_debug', // ID
        __( 'PayPal IPN Debug', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'checkbox_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_paypal_ipn_debug', 'warning' => 'Warning: This will log all transactions the IPN listener receives @uploads/advert-paypal-ipn-logs or @blogs.dir/site#/files/advert-paypal-ipn-logs')
        );

    }

    // Textboxes
    public function ipn_textbox_callback(array $args) {

        global $currentScreen;
        $textbox_id = $args['label_for'];
        printf( '<input type="text" id="advert_paypal_ipn_url" class="advert-span-info" name="paypal_ipn_url" value="' . site_url('?AdVert_PayPal_IPN&action=advert_ipn_handler') . '" readonly>' );     

    }

}// End AdVert_PayPal_Settings Class