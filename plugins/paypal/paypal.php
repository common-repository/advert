<?php

/**
 * Define paypal ipn log for global use
 */
if (!defined('ADVERT_PAYPAL_IPN_LOG_DIR')) {
    $upload_dir = wp_upload_dir();
    define('ADVERT_PAYPAL_IPN_LOG_DIR', $upload_dir['basedir'] . '/advert-paypal-ipn-logs/');
}

require_once( ADVERT_PLUGIN_DIR . 'plugins/paypal/class-advert-paypal-actions.php' ); 
require_once( ADVERT_PLUGIN_DIR . 'plugins/paypal/class-advert-paypal-ipn.php' ); 
require_once( ADVERT_PLUGIN_DIR . 'plugins/paypal/class-advert-paypal-ipn-logging.php' ); 

new AdVert_PayPal_Actions();

if( is_admin() ){
    new AdVert_PayPal_Settings();
}