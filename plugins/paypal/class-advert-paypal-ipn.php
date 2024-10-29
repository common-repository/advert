<?php
/**
 * AdVert PayPal IPN
 *
 * This class defines all code necessary to Paypal IPN functions
 *
 * @since      1.0.0
 * @package    advert
 * @subpackage advert/plugins/paypal
 * @author     me
 */

class AdVert_Paypal_Ipn_Checker {


    /**
     * Constructor for the AdVert_Paypal_Ipn_Checker
     */
    public function __construct() {

        global $advert_options;

        //advert payment plugins
        $advert_paypal_ipn_debug = '';
        if(array_key_exists('advert_paypal_ipn_debug',$advert_options)){
            $advert_paypal_ipn_debug = $advert_options['advert_paypal_ipn_debug'];
        }

        $this->debug     = $advert_paypal_ipn_debug ? 'yes' : 'no';
        //$this->actionurl = (get_option('advert_paypal_ipn_debug') == '1') ? 'yes' : 'no';
        $this->liveurl   = 'https://www.paypal.com/cgi-bin/webscr';
        $this->testurl   = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        // Logs
        if ('yes' == $this->debug) {
            $this->log = new AdVert_Paypal_Ipn_Logger();
        }
    }


    public function advert_check_ipn_request() {
        /**
         * Check for PayPal IPN Response
         */
        @ob_clean();

        $ipn_response = !empty($_POST) ? $_POST : false;

        $return = $this->advert_check_valid_ipn($ipn_response);

        if (isset($return['validate']) && ($return['validate'] == 'required_to_check')) {

            // If $_POST is empty return without process
            if ($ipn_response == false) {
                return false;
            }

            if ($ipn_response && $this->advert_check_if_ipn_request_is_valid($ipn_response)) {

                header('HTTP/1.1 200 OK');

                do_action("advert_valid_ipn_request", $ipn_response);

                return true;
            } else {

                do_action("advert_ipn_request_failed", "PayPal IPN Request Failure", array('response' => 200));

                return false;
            }
        }

        return $return;

    }


    public function advert_check_valid_ipn($posted = null) {

        $return = array();
        $txn_type = (isset($posted['txn_type'])) ? $posted['txn_type'] : '';
        $reason_code = (isset($posted['reason_code'])) ? $posted['reason_code'] : '';
        $payment_status = (isset($posted['payment_status'])) ? $posted['payment_status'] : '';
        $account_key = (isset($posted['account_key'])) ? $posted['account_key'] : '';
        $transaction_type = (isset($posted['transaction_type'])) ? $posted['transaction_type'] : '';

        if(strtoupper($transaction_type) == 'ADAPTIVE PAYMENT PREAPPROVAL' || strtoupper($transaction_type) == 'ADAPTIVE PAYMENT PAY' || !empty($account_key)){

            if($posted == false){
                return false;
            }        

            $raw_post_data  = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $myPost         = array();

            foreach($raw_post_array as $keyval){
	            $keyval = explode ('=', $keyval);
	            if (count($keyval) == 2)
		            $myPost[$keyval[0]] = urldecode($keyval[1]);
            }

            // read the post from PayPal system and add 'cmd'
            $req = 'cmd=_notify-validate';
            if(function_exists('get_magic_quotes_gpc')){
	            $get_magic_quotes_exists = true;
            }

            foreach($myPost as $key => $value){
	            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
		            $value = urlencode(stripslashes($value));
	            } 
                else{
		            $value = urlencode($value);
	            }
	            $req .= "&$key=$value";
            }

            $is_sandbox = (isset($posted['test_ipn'])) ? 'yes' : 'no';

            if('yes' == $is_sandbox){
                $paypal_url = $this->testurl;
            } 
            else{
                $paypal_url = $this->liveurl;
            }

            $ch = curl_init($paypal_url);

            if($ch == FALSE){
                return FALSE;
            }

            $is_enable_curl = function_exists('curl_init') ? true : false;

            if($is_enable_curl == false){
                if('yes' == $this->debug){
                    $this->log->add('paypal', "cURL is not enabled", true);
                }
            }

            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

            if('yes' == $this->debug){
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
            }

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        
            $res = curl_exec($ch);
            if(curl_errno($ch) != 0){ // cURL error
                if('yes' == $this->debug){
                    $this->log->add('paypal', "Can't connect to PayPal to validate IPN message: " . print_r(curl_error($ch), true));
                }
                curl_close($ch);
                exit;
            } 
            else{
                if('yes' == $this->debug){
                    $this->log->add('paypal', 'HTTP response of validation request: ' . print_r($req, true));
                }
                curl_close($ch);
            }

            $tokens = explode("\r\n\r\n", trim($res));
            $res = trim(end($tokens));

            if(strcmp($res, "VERIFIED") == 0){
                if('yes' == $this->debug){
                    $this->log->add('paypal', 'Verified IPN: ' . print_r($res, true));
                }
                return true;
            }
            else if(strcmp($res, "INVALID") == 0){

                if ('yes' == $this->debug) {
                    $this->log->add('paypal', 'Invalid IPN: ' . print_r($res, true));
                }
                return false;

            }
        }
        else{

            $return['validate'] = 'required_to_check';
            return $return;

        }


    }


    /**
     * check_ipn_request_is_valid helper function use when IPN response is valid
     * @since    1.0.0
     * return boolean
     */
    public function advert_check_if_ipn_request_is_valid($ipn_response) {

        if ('yes' == $this->debug) {
            //$this->log->add('paypal', 'IPN advert_ipn_forwarding_handler: ' . print_r($ipn_response, true));
        }

        $is_sandbox = (isset($ipn_response['test_ipn'])) ? 'yes' : 'no';

        if ('yes' == $is_sandbox) {
            $paypal_adr = $this->testurl;
        } else {
            $paypal_adr = $this->liveurl;
        }

        if ('yes' == $this->debug) {
            $this->log->add('paypal', 'Checking IPN response is valid via ' . $paypal_adr . '...');
        }

        // Get received values from post data
        $validate_ipn = array('cmd' => '_notify-validate');
        $validate_ipn += stripslashes_deep($ipn_response);

        // Send back post vars to paypal
        $params = array(
            'body' => $validate_ipn,
            'sslverify' => false,
            'timeout' => 60,
            'httpversion' => '1.0.0',
            'compress' => false,
            'decompress' => false,
            'user-agent' => 'paypal-ipn/'
        );

        if ('yes' == $this->debug) {
            $this->log->add('paypal', 'IPN Request: ' . print_r($params, true));
        }

        // Post back to get a response
        $response = wp_remote_post($paypal_adr, $params);

        if ('yes' == $this->debug) {
            $this->log->add('paypal', 'IPN Response: ' . print_r($response, true));
        }

        // check to see if the request was valid
        if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {
            if ('yes' == $this->debug) {
                $this->log->add('paypal', 'Received valid response from PayPal');
            }

            return true;
        }

        if ('yes' == $this->debug) {
            $this->log->add('paypal', 'Received invalid response from PayPal');
            if (is_wp_error($response)) {
                $this->log->add('paypal', 'Error response: ' . $response->get_error_message());
            }
        }

        return false;
    }


    public function advert_successful_request($IPN_status) {

        $ipn_response = !empty($_POST) ? $_POST : false;

        if ('yes' == $this->debug) {
        	$this->log->add('paypal', 'Payment IPN Array: ' . print_r($ipn_response, true));
        }

        // If $_POST is empty return without process
        if ($ipn_response == false) {
            return false;
        }

        $ipn_response['IPN_status'] = ( $IPN_status == true ) ? 'Verified' : 'Invalid';

        if ('yes' == $this->debug) {
            $this->log->add('paypal', 'Payment IPN_status: ' . $IPN_status);
        }

        $txn_type         = (isset($ipn_response['txn_type'])) ? $ipn_response['txn_type'] : '';
        $reason_code      = (isset($ipn_response['reason_code'])) ? $ipn_response['reason_code'] : '';
        $payment_status   = (isset($ipn_response['payment_status'])) ? $ipn_response['payment_status'] : '';
        $account_key      = (isset($ipn_response['account_key'])) ? $ipn_response['account_key'] : '';
        $transaction_type = (isset($ipn_response['transaction_type'])) ? $ipn_response['transaction_type'] : '';

        if(strtoupper($transaction_type) == 'ADAPTIVE PAYMENT PREAPPROVAL' || strtoupper($transaction_type) == 'ADAPTIVE PAYMENT PAY' || !empty($account_key)){
            $posted = $this->advert_decode_ipn();
            $posted['IPN_status'] = $ipn_response['IPN_status'];
            $posted = stripslashes_deep($posted);
        }
        else{
            $posted = stripslashes_deep($ipn_response);
        }

        if(isset($posted['txn_type']) && $posted['txn_type'] == 'masspay'){

            $i = 1;
            $postedmasspay = array();
            while (isset($posted['masspay_txn_id_' . $i])) {
                $masspay_txn_id = isset($posted['masspay_txn_id_' . $i]) ? $posted['masspay_txn_id_' . $i] : '';
                $mc_currency    = isset($posted['mc_currency_' . $i]) ? $posted['mc_currency_' . $i] : '';
                $mc_fee         = isset($posted['mc_fee_' . $i]) ? $posted['mc_fee_' . $i] : 0;
                $mc_gross       = isset($posted['mc_gross_' . $i]) ? $posted['mc_gross_' . $i] : 0;
                $receiver_email = isset($posted['receiver_email_' . $i]) ? $posted['receiver_email_' . $i] : '';
                $status         = isset($posted['status_' . $i]) ? $posted['status_' . $i] : '';
                $unique_id      = isset($posted['unique_id_' . $i]) ? $posted['unique_id_' . $i] : '';

                $postedmasspay = array(
                    'masspay_txn_id'      => $masspay_txn_id,
                    'mc_currency'         => $mc_currency,
                    'mc_fee'              => $mc_fee,
                    'mc_gross'            => $mc_gross,
                    'receiver_email'      => $receiver_email,
                    'status'              => $status,
                    'unique_id'           => $unique_id,
                    'payment_date'        => $posted['payment_date'],
                    'payment_status'      => $posted['payment_status'],
                    'charset'             => $posted['charset'],
                    'first_name'          => $posted['first_name'],
                    'notify_version'      => $posted['notify_version'],
                    'payer_status'        => $posted['payer_status'],
                    'verify_sign'         => $posted['verify_sign'],
                    'payer_email'         => $posted['payer_email'],
                    'payer_business_name' => $posted['payer_business_name'],
                    'last_name'           => $posted['last_name'],
                    'txn_type'            => $posted['txn_type'],
                    'residence_country'   => $posted['residence_country'],
                    'ipn_track_id'        => $posted['ipn_track_id'],
                    'IPN_status'          => $ipn_response['IPN_status']
                );

                $this->successfull_request_handler($postedmasspay);
                //$this->ipn_response_data_handler($postedmasspay);

                $i++;
            }
        } else {

            $i = 1;
            $cart_items = array();
            while (isset($posted['item_number' . $i])) {
                $item_number       = isset($posted['item_number' . $i]) ? $posted['item_number' . $i] : '';
                $item_name         = isset($posted['item_name' . $i]) ? $posted['item_name' . $i] : '';
                $quantity          = isset($posted['quantity' . $i]) ? $posted['quantity' . $i] : '';
                $mc_gross          = isset($posted['mc_gross_' . $i]) ? $posted['mc_gross_' . $i] : 0;
                $mc_handling       = isset($posted['mc_handling' . $i]) ? $posted['mc_handling' . $i] : 0;
                $mc_shipping       = isset($posted['mc_shipping' . $i]) ? $posted['mc_shipping' . $i] : 0;
                $custom            = isset($posted['custom' . $i]) ? $posted['custom' . $i] : '';
                $option_name1      = isset($posted['option_name1_' . $i]) ? $posted['option_name1_' . $i] : '';
                $option_selection1 = isset($posted['option_selection1_' . $i]) ? $posted['option_selection1_' . $i] : '';
                $option_name2      = isset($posted['option_name2_' . $i]) ? $posted['option_name2_' . $i] : '';
                $option_selection2 = isset($posted['option_selection2_' . $i]) ? $posted['option_selection2_' . $i] : '';
                $btn_id            = isset($posted['btn_id' . $i]) ? $posted['btn_id' . $i] : '';
                $tax               = isset($posted['tax' . $i]) ? $posted['tax' . $i] : '';

                $current_item = array(
                    'item_number'       => $item_number,
                    'item_name'         => $item_name,
                    'quantity'          => $quantity,
                    'mc_gross'          => $mc_gross,
                    'mc_handling'       => $mc_handling,
                    'mc_shipping'       => $mc_shipping,
                    'custom'            => $custom,
                    'option_name1'      => $option_name1,
                    'option_selection1' => $option_selection1,
                    'option_name2'      => $option_name2,
                    'option_selection2' => $option_selection2,
                    'btn_id'            => $btn_id,
                    'tax'               => $tax
                );

                array_push($cart_items, $current_item);
                $i++;
            }

            // If cart_items is not emptry
            if (is_array($cart_items) && !empty($cart_items)) {
                $posted['cart_items'] = $cart_items;
            }

            $this->successfull_request_handler($posted);
            //$this->ipn_response_data_handler($posted);
        }

    }


    public function advert_decode_ipn() {
        $raw_post = file_get_contents("php://input");
        if (empty($raw_post)) {
            return array();
        } // else:
        $post = array();
        $pairs = explode('&', $raw_post);
        foreach ($pairs as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $key = urldecode($key);
            $value = urldecode($value);
            // This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
            preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
            switch (count($key_parts)) {
                case 4:
                    // Original key format: somekey[x].property
                    // Converting to $post[somekey][x][property]
                    if (!isset($post[$key_parts[1]])) {
                        $post[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
                    } else if (!isset($post[$key_parts[1]][$key_parts[2]])) {
                        $post[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
                    } else {
                        $post[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                    }
                    break;
                case 3:
                    // Original key format: somekey[x]
                    // Converting to $post[somkey][x]
                    if (!isset($post[$key_parts[1]])) {
                        $post[$key_parts[1]] = array();
                    }
                    $post[$key_parts[1]][$key_parts[2]] = $value;
                    break;
                default:
                    // No special format
                    $post[$key] = $value;
                    break;
            }
        }

        return $post;
    }


    public function successfull_request_handler($posted = null) {

        if (isset($posted['payment_status']) && !empty($posted['payment_status'])) {

            if ('yes' == $this->debug) {
                $this->log->add('paypal', 'Payment status: ' . $posted['payment_status']);
            }

            /* developers to trigger their own functions based on different payment_status values received by PayPal IPN's.
             * $posted array contain all the response variable from received by PayPal IPN's
             */

            if (isset($posted['txn_type']) && $posted['txn_type'] != 'masspay') {
                do_action('advert_payment_status_' . strtolower($posted['payment_status']), $posted);
            } else {
                do_action('advert_masspay_payment_status_' . strtolower($posted['payment_status']), $posted);
            }

        }

        if (isset($posted['status']) && !empty($posted['status'])) {

            if ('yes' == $this->debug) {
                $this->log->add('paypal', 'Payment status: ' . $posted['status']);
            }

            /* developers to trigger their own functions based on different status values received by PayPal IPN's.
             * $posted array contain all the response variable from received by PayPal IPN's
             */

            do_action('advert_adaptive_status_' . strtolower(str_replace(' ', '_', $posted['status'])), $posted);
        }

        if (isset($posted['txn_type']) && !empty($posted['txn_type'])) {

            if ('yes' == $this->debug) {
                $this->log->add('paypal', 'Payment transaction type: ' . $posted['txn_type']);
            }

            /* developers to trigger their own functions based on different txn_type values received by PayPal IPN's.
             * $posted array contain all the response variable from received by PayPal IPN's
             */

            do_action('advert_txn_type_' . strtolower($posted['txn_type']), $posted);
        }

        if (isset($posted['transaction_type']) && !empty($posted['transaction_type'])) {

            if ('yes' == $this->debug) {
                $this->log->add('paypal', 'Payment transaction type: ' . $posted['transaction_type']);
            }

            /* developers to trigger their own functions based on different transaction_type values received by PayPal IPN's.
             * $posted array contain all the response variable from received by PayPal IPN's
             */

            if ($posted['transaction_type'] == 'Adjustment' || $posted['transaction_type'] = 'Adaptive Payment PAY' || $posted['transaction_type'] = 'Adaptive Payment Pay') {
                do_action('advert_adaptive_' . strtolower(str_replace(' ', '_', $posted['transaction_type'])), $posted);
            }
        }

    }


    function advert_exist_post_by_title($ipn_txn_id) {

        global $wpdb;

        $post_data = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s ", $ipn_txn_id, 'paypal_ipn'));

        if (empty($post_data)) {

            return false;
        } else {

            return $post_data[0];
        }
    }


} // End AdVert_Paypal_Ipn_Checker Class