<?php

class AdVert_Custom_Actions {

    public function __construct() {

        //add an action to display the payment to the advertiser
        add_action('advert_payment_type', array($this, 'advert_create_custom')); 

    }

    public function advert_create_custom(){

        global $advert_options;

        //advert payment plugins
        $custom1 = '';
        $custom2 = '';

        if(array_key_exists('advert_custom_textbox1',$advert_options)){
            $custom1 = htmlspecialchars_decode($advert_options['advert_custom_textbox1']);
        }

        if(array_key_exists('advert_custom_textbox2',$advert_options)){
            $custom2 = htmlspecialchars_decode($advert_options['advert_custom_textbox2']);
        }

        printf( $custom1 . $custom2 );

    }


}// End AdVert_Custom_Actions Class


class AdVert_Custom_Settings extends AdVert_Control_Panel {

    public function __construct() {

        //add action for advert settings page
        add_action('advert_add_payment_settings_custom', array($this, 'advert_add_custom_payment_settings'));

        //array for verification
        add_filter('advert_no_sanitize', array( $this, 'advert_no_sanitize' ), 1 );

    }

    public function advert_no_sanitize(){
        
        $plugnosanitize = array(
        'advert_custom_textbox1',
        'advert_custom_textbox2'
        );

        return $plugnosanitize;

    }

    public function advert_add_custom_payment_settings(){
     
        add_settings_field(
        'advert_custom_textbox1', // ID
        __( 'Custom 1', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textarea_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_custom_textbox1')
        );

        add_settings_field(
        'advert_custom_textbox2', // ID
        __( 'Custom 2', 'ADVERT_TEXTDOMAIN' ), // Title 
        array( $this, 'textarea_callback' ), // Callback
        'advert-cp-general', // Page
        'advert_options_cp_general_s2', // Section
        $selectOptions = array ('label_for' => 'advert_custom_textbox2')
        );

    }


}// End AdVert_Custom_Settings Class