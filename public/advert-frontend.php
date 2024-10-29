<?php

add_action('plugins_loaded', 'advert_frontend_start');


function advert_frontend_start() {

    global $advert_options;

    $is_advert_off = 0;
    if( array_key_exists('advert_turn_off', $advert_options)){
        $is_advert_off = intval($advert_options['advert_turn_off']);
    }

    //if the options is selected in the control panel, AdVert will not display Ads
    if( $is_advert_off === 1 ){
        return;
    }

    require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-shortcode.php' );    
    require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-log.php' );
    require_once( ADVERT_PLUGIN_DIR . 'public/advert-frontend-functions.php' );

    require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-payments.php' );
        $paymentfilter = new AdVert_Payments;
        $paymentfilter->advert_create_payment_filters();

    require_once( ADVERT_PLUGIN_DIR . 'includes/advert-ajax.php' );
    require_once( ADVERT_PLUGIN_DIR . 'includes/class-advert-widget.php' );

}