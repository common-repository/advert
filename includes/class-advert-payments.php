<?php
/**
 * AdVert Payment Actions
 *
 * @since 1.0.0
 */
class AdVert_Payments {

    public function advert_create_payment_filters() {

        add_filter('add_adcredits', array($this, 'advert_add_adcredit'), 10, 5);
        add_filter('remove_adcredits', array($this, 'advert_remove_adcredit'), 10, 5);
        add_filter('trans_adcredits', array($this, 'advert_trans_history'), 10, 4);
        add_action('calc_adcredits', array($this, 'advert_calc_adcredits'), 10, 1);

        add_filter('fixed_campaign', array($this, 'advert_fixed_campaign'), 10, 1);
        add_filter('perday_campaign', array($this, 'advert_perday_campaign'), 10, 1);

        add_filter('advert_get_transaction_id', array($this, 'advert_transID'), 10, 1);
        add_filter('advert_check_transaction_id', array($this, 'advert_check_trans_id'), 10, 1);

    }


    public function advert_fixed_campaign($campaign_id){

        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_logged';

        //check total amount of charges for the campaign
        $fixed_campaign = $wpdb->get_var(" SELECT SUM(price) FROM $table_name WHERE camp_id = $campaign_id ");

        return $fixed_campaign;

    }


    public function advert_perday_campaign($campaign_id){

        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_logged';

        //check todays charges for the campaign
        $today = date('Y-m-d 00:00:00',strtotime(current_time('mysql')));
        $perday_campaign = $wpdb->get_var(" SELECT SUM(price) FROM $table_name WHERE camp_id = $campaign_id AND time >= '{$today}' ");

        return $perday_campaign;

    }


    public function advert_add_adcredit($id, $amount, $timestamp = '', $reason = '', $transaction_id = ''){

        if( !empty($id) && !empty($amount) && get_post_status($id) ){

            $name        = get_post_meta($id, 'advertiser_company', true);     
            $add_credits = str_replace('-', '', number_format($amount, 2));
            $timestamp   = ( empty($timestamp) ? current_time('mysql') : $timestamp );

            $this->advert_insert_row($id, $name, $add_credits, '', $timestamp, $reason, $transaction_id);

        return TRUE;

        }

    }


    public function advert_remove_adcredit($id, $amount, $timestamp = '', $reason = '', $transaction_id = ''){
    
        if( !empty($id) && !empty($amount) && get_post_status($id) ){

            $name           = get_post_meta($id, 'advertiser_company', true);     
            $remove_credits = str_replace('-', '', number_format($amount, 2));
            $timestamp      = ( empty($timestamp) ? current_time('mysql') : $timestamp );

            $this->advert_insert_row($id, $name, '', $remove_credits, $timestamp, $reason, $transaction_id);

        return TRUE;

        }

    }


    public function advert_insert_row($adv_id, $adv_name, $added = 0, $removed = 0, $time, $reason, $transaction_id){
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_payment';

        $wpdb->insert(
            $table_name, 
		    array( 
			    'adv_id'   => $adv_id, 
			    'adv_name' => $adv_name,
			    'added'    => $added,
			    'removed'  => $removed,
			    'time'     => $time,
                'reason'   => $reason,
                'trans_id' => $transaction_id
		    ),
		    array( 
			    '%d', 
			    '%s', 
			    '%f',
			    '%f',
			    '%s',
			    '%s',
			    '%s'
		    ) 
        );


    }


    public function advert_transID($adv_id){
        
        $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $trans_id = '';
        $length   = 12;
        //$tstamp   = time();

        $count = strlen($charset);

        while ($length--) {
            $trans_id .= $charset[mt_rand(0, $count-1)];
        }

        $trans_id .= $adv_id;

        return $trans_id;

    }


    public function advert_check_trans_id($trans_id){
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'advert_payment';

        //check for transaction id match
        $trand_id_check = $wpdb->get_var(" SELECT COUNT(trans_id) FROM $table_name WHERE trans_id = $trans_id ");

        return $trand_id_check;

    }


    public function advert_calc_adcredits($post_id){
  
        if( !empty($post_id) && get_post_status($post_id) ){

            global $wpdb;
            $table_name = $wpdb->prefix . 'advert_payment';
            $totalamount = $wpdb->get_results(" SELECT ( SUM(added) - SUM(removed) ) AS adcredits FROM $table_name WHERE adv_id = $post_id ");
            update_post_meta($post_id, 'company_credits', $totalamount[0]->adcredits );    

        }

    }


    public function advert_trans_history($post_id = '', $howmany = '', $exclude_ads = '', $exclude_payments = ''){
   
            global $wpdb;
            $table_name = $wpdb->prefix . 'advert_payment';
            $cur_adv = '';
            $exclude = '';

            if( !empty($post_id) && get_post_status($post_id) ){
                $adv_id  = 'WHERE adv_id = ' . $post_id;
                $cur_adv = $post_id;
            }

            if($exclude_ads == TRUE){
                if(!empty($post_id) && get_post_status($post_id)){
                    $exclude = "AND (reason <> 'i' AND reason <> 'c')";
                }
                else{
                    $exclude = "WHERE (reason <> 'i' AND reason <> 'c')";
                }
            }

            if($exclude_payments == TRUE){
                if(!empty($post_id) && get_post_status($post_id)){
                    $exclude = "AND (reason = 'i' OR reason = 'c')";
                }
                else{
                    $exclude = "WHERE (reason = 'i' OR reason = 'c')"; 
                }
            }

            $query          = ( !empty($post_id) && get_post_status($post_id) ) ? "SELECT * FROM $table_name $adv_id $exclude" : "SELECT * FROM $table_name $exclude";
            $total_query    = "SELECT COUNT(1) FROM (${query}) AS combined_table";
            $total          = $wpdb->get_var( $total_query );
            $items_per_page = (!empty($howmany) && intval($howmany) > 0) ? $howmany : 25;
            $page           = isset( $_GET['transpage'] ) ? abs( (int) $_GET['transpage'] ) : 1;
            $offset         = ( $page * $items_per_page ) - $items_per_page;

            if(!empty($howmany) && intval($howmany) > 0){
                $querytrans     = $wpdb->get_results( $query . " ORDER BY time DESC LIMIT ${offset}, ${items_per_page}" );
                $totalPage      = 1;
            }
            else{
                $querytrans     = $wpdb->get_results( $query . " ORDER BY time DESC LIMIT ${offset}, ${items_per_page}" );
                $totalPage      = ceil($total / $items_per_page);
            }

            $transArray[] = $querytrans;
            $transArray[] = $page;
            $transArray[] = $totalPage;
            $transArray[] = $cur_adv;

            return $transArray;


    }


}// End Advert Payments Class