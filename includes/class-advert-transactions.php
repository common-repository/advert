<?php
/**
 * AdVert Transactions
 *
 * @since 1.0.0
 */
class AdVert_Transactions {


    private $transaction_id;
    private $user_id;
    private $howmany;
    private $adv_id;

    public function __construct() {

        $this->advert_get_transactions_id();
        $this->advert_do_transactions();

    }


    public function advert_get_transactions_id(){

        //who are we working with
        $this->user_id = $user_id = intval(get_current_user_id());
        $this->howmany = $howmany = intval(apply_filters('check_advertiser', $user_id));
        $this->adv_id  = $adv_id = intval(apply_filters('get_advertiser_id', $user_id));      

        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && $_POST['post_type'] == 'admin_transaction_filter'  && $_POST['originalaction'] == 'admintransaction' && current_user_can('edit_adverts') ){
            if ( !is_user_logged_in() || ! wp_verify_nonce( $_POST['admin-transactions'], 'admin_transactions' ) ){
                print 'Woah, whats really going on...?';
                return;
            }

            if( isset($_POST['advert-transaction-filter']) ){
                
                if(current_user_can('edit_adverts') && !current_user_can('publish_adverts') && $howmany > 0){
                    $this->transaction_id = $adv_id;
                }
                elseif(current_user_can('publish_adverts')){
                    $this->transaction_id = sanitize_text_field($_POST['advert-transaction-filter']);
                }

            }
          
        }
        elseif(isset($_GET['transadv']) && intval($_GET['transadv']) > 0){
            if ( isset($_GET['_nonce']) && wp_verify_nonce( $_GET['_nonce'], 'advert-transactions-link' ) ){

                if(current_user_can('edit_adverts') && !current_user_can('publish_adverts') && $howmany > 0){
                    $this->transaction_id = $adv_id;
                }
                elseif(current_user_can('publish_adverts')){
                    $this->transaction_id = $_GET['transadv'];
                }
          
            }
            else{
                print 'Woah, whats really going on...?';
                return;
            }
        }    

    }


    public function advert_do_transactions(){

    $paged = '';

    ?>


        <div class="wrap theme-settings-wrap">

            <div class="advert-page-heading-logo">a</div>

            <h2><?php _e('Transactions', 'ADVERT_TEXTDOMAIN');?></h2>

            <div class="wrap advert-analysis-wrap" class="wrap">

                <div class="advert-additional-wrap">
                    <div class="advert-select-wrap fright">
                    <form id="advert-analysis-overview-filter" action="<?php echo admin_url('admin.php?page=advert-transactions'); ?>" method="post">

                    <?php wp_nonce_field( 'admin_transactions', 'admin-transactions' ); ?>
                    <?php wp_get_referer() ?>
                    <input type="hidden" id="hiddenaction" name="action" value="admintransactionfilter" />
                    <input type="hidden" id="originalaction" name="originalaction" value="admintransaction" />
                    <input type="hidden" id="post_type" name="post_type" value="admin_transaction_filter" />
                    
                    <div class="advert-exclude-checker">
                    <label><input type="checkbox" name="advert-exclude-ads" <?php checked( 'on', isset($_POST['advert-exclude-ads']) || isset($_GET['ea']) && $_GET['ea'] == 'on' ? 'on' : 0, true ); ?> >Hide Ads</label>
                    </div>

                    <div class="advert-exclude-checker">
                    <label><input type="checkbox" name="advert-exclude-payments" <?php checked( 'on', isset($_POST['advert-exclude-payments']) || isset($_GET['ep']) && $_GET['ep'] == 'on' ? 'on' : 0, true ); ?> >Hide Payments</label>
                    </div>

                    <?php

                    if(current_user_can('publish_adverts')){

                    $users = get_users();

                    echo '<select id="advert-transaction-filter" name="advert-transaction-filter">';

                        echo '<optgroup label="' . __( 'Basic Transactions', 'ADVERT_TEXTDOMAIN' ) . '">';
                        echo '<option value="recent">' . __( 'Recent Transactions', 'ADVERT_TEXTDOMAIN' ) . '</option>';
                        echo '</optgroup>';
                        echo '<optgroup label="' . __( 'Select an Advertiser', 'ADVERT_TEXTDOMAIN' ) . '">';
                        foreach($users as $user){
                            $adv_id  = intval(apply_filters('get_advertiser_id', $user->ID));  
                            $howmany = intval(apply_filters('check_advertiser', $user->ID));
                            if(user_can($user->ID, 'edit_adverts') && !user_can($user->ID, 'publish_adverts') && $howmany > 0){
                                if(isset($_POST['advert-transaction-filter']) && sanitize_text_field($_POST['advert-transaction-filter']) == $adv_id || isset($_GET['transadv']) && $_GET['transadv'] == $adv_id){
                                    echo '<option value="'.$adv_id.'" selected>'.$user->user_login.'</option>';                                    
                                }
                                else{                
                                    echo '<option value="'.$adv_id.'">'.$user->user_login.'</option>';
                                }
                            }
                        }
                        echo '</optgroup>';

                    echo '</select>';
                    }

                    //elseif(current_user_can('edit_adverts') && !current_user_can('publish_adverts') && $this->howmany > 0){
                
                    //echo '<option value="'.$this->adv_id.'">'.get_the_title($this->adv_id).'</option>';

                    //}

                    ?>
                    


                    <input type="submit" name="filter_action" id="advert-overview-query-submit" value="Filter">



                    </form>

                    </div>

                    <div class="clear"></div>


                    <div id="advert-transaction-history-wrap">
                    <div id="advert-transaction-history-fixed-head">
                    <table cellspacing="0">
                    <thead>
                    <tr>
                    <th width="80px"><?php _e( 'Advertiser', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="100px"><?php _e( 'Trans ID', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="80px"><?php _e( 'AdCredits', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="100px"><?php _e( 'Action', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="120px"><?php _e( 'Reason', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="120px"><?php _e( 'Timestamp', 'ADVERT_TEXTDOMAIN'); ?></th>
                    </tr>
                    </thead>
                    </table>
                    </div>

                    <div id="advert-transaction-history-data">
                    <table cellspacing="0">
                    <thead>
                    <tr>
                    <th width="80px"><?php _e( 'Advertiser', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="100px"><?php _e( 'Trans ID', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="80px"><?php _e( 'AdCredits', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="100px"><?php _e( 'Action', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="120px"><?php _e( 'Reason', 'ADVERT_TEXTDOMAIN'); ?></th>
                    <th width="120px"><?php _e( 'Timestamp', 'ADVERT_TEXTDOMAIN'); ?></th>
                    </tr>
                    </thead>
                    <tbody style="text-align:center">

                    <?php

                    //$filter = new AdVert_Payments();
                    $trans_id     = '';
                    $trans_recent = '';

                    if(empty($this->transaction_id) || $this->transaction_id === 'recent'){

                        if(current_user_can('edit_adverts') && !current_user_can('publish_adverts') && $this->howmany > 0){
                            $trans_id     = $this->adv_id;
                            //$trans_recent = '25';
                        }
                        elseif(current_user_can('publish_adverts')){
                            $trans_recent = '25';                            
                        }

                    }
                    else{
                        
                        if(current_user_can('edit_adverts') && !current_user_can('publish_adverts') && $this->howmany > 0){
                            $trans_id     = $this->adv_id;
                        }
                        elseif(current_user_can('publish_adverts')){
                            $trans_id     = $this->transaction_id;                         
                        }
                    }

                    $ads = '';
                    $payments = '';

                    if( isset($_POST['advert-exclude-ads']) && $_POST['advert-exclude-ads'] == 'on' || isset($_GET['ea']) && $_GET['ea'] == 'on' ){
                        $ads = TRUE;
                    }

                    if( isset($_POST['advert-exclude-payments']) && $_POST['advert-exclude-payments'] == 'on' || isset($_GET['ep']) && $_GET['ep'] == 'on'){
                        $payments = TRUE;
                    }

                    $transaction_history = apply_filters('trans_adcredits', $trans_id, $trans_recent, $ads, $payments);    

                    $count = 1;
                    if( !empty($transaction_history)){  
                        foreach( $transaction_history[0] as $transaction ){
              
                            if($count === 1){
                                $trbg = 'advert-trbg-gray';
                                $count = 2;
                            }
                            else{
                                $trbg = '';
                                $count = 1;                          
                            }

                            if ($transaction->removed > 0){
                                echo '<tr class="ad-credit-removed '.$trbg.'"><td>'.$transaction->adv_name.'</td><td>'.$transaction->trans_id.'</td><td>'.number_format_i18n( str_replace("-", "", $transaction->removed), 2 ).'</td><td>'. __( 'Removed', 'ADVERT_TEXTDOMAIN') .'</td><td>'. esc_html($transaction->reason) .'</td><td>'. date_i18n( __( 'm/j/Y - G:i:s' ), strtotime($transaction->time) ) .'</td></tr>';
                            }

                            else{
                                echo '<tr class="ad-credit-received '.$trbg.'"><td>'.$transaction->adv_name.'</td><td>'.$transaction->trans_id.'</td><td>'.number_format_i18n( str_replace("-", "", $transaction->added), 2 ).'</td><td>'. __( 'Added', 'ADVERT_TEXTDOMAIN') .'</td><td>'. esc_html($transaction->reason) .'</td><td>'. date_i18n( __( 'm/j/Y - G:i:s' ), strtotime($transaction->time) ) .'</td></tr>';
                            }

                        }

                    if($transaction_history[2] > 1){
                        //who are we working with
                        $user_id = intval(get_current_user_id());
                        $adv_id  = intval(apply_filters('get_advertiser_id', $user_id));   
                        $cur_adv = current_user_can('publish_adverts') ? $transaction_history[3] : $adv_id;
                        $ea = isset($_POST['advert-exclude-ads']) ? $_POST['advert-exclude-ads'] : 0;
                        $ep = isset($_POST['advert-exclude-payments']) ? $_POST['advert-exclude-payments'] : 0;
                        $paged   = '<div class="advert-paged"><span class="advert-paged-left">Page '.$transaction_history[1].' of '.$transaction_history[2].'</span><span class="advert-paged-right">'.paginate_links( array(
                        'base'      => add_query_arg( array('transpage' => '%#%', 'transadv' => $cur_adv, 'ea' => $ea, 'ep' => $ep, '_nonce' => wp_create_nonce( 'advert-transactions-link' )) ),
                        'format'    => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total'     => $transaction_history[2],
                        'current'   => $transaction_history[1]
                        )).'</span></div>';
                    }

                    }  

                    ?>

    </tbody>
    </table>
    </div>
    <?php echo $paged; ?>
    </div>


                </div>
            </div>
        </div>


    <?php

    }


}// End Advert Transactions Class

new AdVert_Transactions();