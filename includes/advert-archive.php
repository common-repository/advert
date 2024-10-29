<?php

function advert_post_data(){

    //archive post
    if ( isset( $_GET['post'] ) ){

        $nonce = $_REQUEST['_wpnonce'];
        if ( wp_verify_nonce( $nonce, 'advert-archive' ) && current_user_can('publish_adverts') ){

            $post_id = $post_ID = (int) $_GET['post'];
            $action = $_GET['action'];

            switch($action){
                case 'archive':
                $update_advert = array(
                    'ID'          => $post_id,
                    'post_status' => 'advert-archive',
                );
                wp_update_post( $update_advert );

                //automatically release any funds leftover
                if( get_post_type($post_id) === 'advert-campaign'){
                    $campaign_charges       = get_post_meta($post_id, 'campaign_charges' , true);
                    $campaign_budget_value  = get_post_meta($post_id, 'campaign_budget_value', true);

                    $campaign_charges = ( !empty($campaign_charges) ? number_format($campaign_charges, 2) : 0 );

                    $lessCharges = ( number_format($campaign_budget_value, 2) - number_format($campaign_charges, 2) );  

                    if($lessCharges > 0 && $campaign_budget_value >= $lessCharges){
                        update_post_meta($post_id, 'campaign_budget_value', number_format($campaign_charges, 2) );
                    }
                }

                wp_redirect( wp_get_referer() );
                exit;
                case 'unarchive':
                $update_advert = array(
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                );
                wp_update_post( $update_advert );
                wp_redirect( wp_get_referer() );
                exit;
            }//end switch

        }

    }

}