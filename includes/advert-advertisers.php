<?php

function advertiser_start($post){

    global $advert_options;

    $company = get_post_meta($post->ID, 'advertiser_company', true);

    add_meta_box('advert_advertiser', __( 'Advertiser Info', 'ADVERT_TEXTDOMAIN' ), 'advertiser_meta_box', 'advert-advertiser' , 'normal' , 'high' );

    if( !empty($company) ){
        //user_advertmgr_adcredits

        //check options
        $user_advertmgr_adcredits = 0;
        if( array_key_exists('advert_advertmgr_adcredits', $advert_options) ){
            $user_advertmgr_adcredits = intval($advert_options['advert_advertmgr_adcredits']);
        }

        if( current_user_can('publish_adverts') && $user_advertmgr_adcredits === 1 || current_user_can('manage_options') ){
            add_meta_box('advert_advertiser_overview', __( 'Advertiser Overview', 'ADVERT_TEXTDOMAIN' ), 'advertiser_meta_box2', 'advert-advertiser' , 'side' , 'high' );
        }

        add_meta_box('advert_change_ownership', __( 'Change Control', 'ADVERT_TEXTDOMAIN' ), 'advertiser_meta_box3', 'advert-advertiser' , 'side' , 'low' );
        add_meta_box('advert_transaction_history', __( 'Transaction History', 'ADVERT_TEXTDOMAIN' ), 'advertiser_meta_box4', 'advert-advertiser' , 'normal' , 'low' );
    }    

    remove_meta_box('postimagediv','advert-advertiser','side');
    remove_meta_box( 'slugdiv', 'advert-advertiser', 'normal' );
    remove_meta_box( 'submitdiv', 'advert-advertiser', 'side' );

    add_meta_box('submitdiv', __( 'Publishing Tools', 'ADVERT_TEXTDOMAIN' ), 'advert_post_submit_meta_box', 'advert-advertiser', 'side', 'high');

}


function advertiser_meta_box($post){

    wp_nonce_field( 'advertiser_meta_box', 'advertiser_meta_box_nonce' );
    $company = get_post_meta($post->ID , 'advertiser_company' , true);
    $email = get_post_meta($post->ID , 'advertiser_email' , true);
    $telephone = get_post_meta($post->ID , 'advertiser_telephone' , true);
    $notes = get_post_meta($post->ID , 'advertiser_notes' , true);
    $add_credits = get_post_meta($post->ID , 'add_credits' , false);

    ?>

    <p>
    <label for="company"><strong><?php _e( 'Name (Company or Title)', 'ADVERT_TEXTDOMAIN' );?></strong></label><br />
    <input class="meta_advertiser" id="company" type="text" value="<?php echo $company; ?>" name="advertiser_company" required/>
    </p>

    <p>
    <label for="email"><strong><?php _e( 'Email Address', 'ADVERT_TEXTDOMAIN' ); ?> *</strong></label><br />
    <input class="meta_advertiser" id="email" type="email" value="<?php echo $email; ?>" name="advertiser_email" required/>
    </p>

    <p>
    <label for="telephone"><strong><?php _e( 'Telephone Number', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <input class="meta_advertiser" id="telephone" type="tel" value="<?php echo $telephone; ?>"  name="advertiser_telephone"/>
    </p>

    <p>
    <label for="notes"><strong><?php _e( 'Notes', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <textarea class="meta_advertiser" id="notes" name="advertiser_notes" rows="5"><?php echo $notes; ?></textarea>
    </p>

    <?php

}


function advertiser_meta_box2($post){

    $company_credits = get_post_meta($post->ID , 'company_credits' , true);
    $advert_bypass_adcredits = get_post_meta($post->ID , 'advert_bypass_adcredits' , true);

    if ( $advert_bypass_adcredits != 1 ){ 
        
        ?>

        <label for="company_credits"><strong><?php _e('AdCredits Available', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
        <h1><?php if(empty($company_credits)){echo '0';}else{echo number_format($company_credits, 2);} ?></h1>

        <p>
        <label for="add_credits"><strong><?php _e('Add/Remove Ad Credits', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
        <input class="add_credits" id="advert_price" type="number" min="0" step="0.01" name="add_credits"/>
        <select id="add_remove_addcredits" class="add_credits" name="add_remove_adcredits">
        <option value="add"><?php _e('Add', 'ADVERT_TEXTDOMAIN'); ?></option>
        <option value="remove"><?php _e('Remove', 'ADVERT_TEXTDOMAIN'); ?></option>
        </select>
        </p>

        <p id="transaction_reason">
        <label for="transaction_reason"><strong><?php _e('Reason', 'ADVERT_TEXTDOMAIN'); ?></strong></label><br />
        <input name="transaction_reason" type="text">
        </p>

        <?php 
 
    } 
        
    ?>

    <p>
    <label for="advert_bypass_adcredits">
    <input type="checkbox" id="advert_bypass_adcredits" name="advert_bypass_adcredits" value="1" <?php checked( $advert_bypass_adcredits, 1 ); ?> />
    <strong><?php ( $advert_bypass_adcredits == 1 ? _e('AdCredits disabled', 'ADVERT_TEXTDOMAIN') : _e('Disable AdCredits?', 'ADVERT_TEXTDOMAIN') ); ?></strong>
    </label>
    </p>

    <hr>
    <p class="advert-tip"><span class="advert-sm-info"><?php _e('AdVert Tip: If you disable AdCredits for this Advertiser, charges will not occur.', 'ADVERT_TEXTDOMAIN');?></span></p>

    <?php
            
}


function advertiser_meta_box3($post){

    $users = get_users();

    ?>

    <p>
    <span class="campaign_description"><?php _e('Useful when you manually create a new Advertiser and want to attach it to a user on the site.', 'ADVERT_TEXTDOMAIN');?></span><br /><br />
    <select id="add_change_owner"  name="add_change_owner">

    <?php 
 
    foreach($users as $user){

        $args = array('author' => $user->ID, 'post_type' => 'advert-advertiser', 'post_status' => 'any');
        $all_posts = new WP_Query($args);
        $allposts = $all_posts->found_posts;

        if($allposts){
            $post_id = $all_posts->posts[0]->ID;
        }
        else{
            $post_id = '';    
        }

        wp_reset_postdata();

        if(intval($post_id) === intval($post->ID)){
            echo '<option value="'.$user->ID.'" selected="selected">'.$user->user_login.'</option>';
        }

        elseif(user_can($user->ID, 'publish_adverts')){
            echo '<option value="'.$user->ID.'">'.$user->user_login.'</option>';  
        }

        elseif($allposts > 0){
            echo '<option value="'.$user->ID.'" disabled="disabled">'.$user->user_login.'</option>';  
        }

        else{
            echo '<option value="'.$user->ID.'">'.$user->user_login.'</option>';    
        }

    }

    ?>
    
    </select>
    </p>

    <p>
    Current Control: <?php if ( !user_can( $post->post_author, 'publish_adverts' )){echo _e('Advertiser', 'ADVERT_TEXTDOMAIN');}else{ _e('AdVert Manager', 'ADVERT_TEXTDOMAIN'); } ?>
    </p>

    <hr>

    <p class="advert-tip"><span class="advert-sm-info"><?php _e('AdVert Tip: When you change ownership to an existing user, the users role is changed to Advertiser.', 'ADVERT_TEXTDOMAIN');?></span></p>

    <?php
        
}


function advertiser_meta_box4($post){

    ?>
    
    <div id="advert-transaction-history-wrap">
    <div id="advert-transaction-history-fixed-head">
    <table cellspacing="0">
    <thead>
    <tr>
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
    <th width="80px"><?php _e( 'AdCredits', 'ADVERT_TEXTDOMAIN'); ?></th>
    <th width="100px"><?php _e( 'Action', 'ADVERT_TEXTDOMAIN'); ?></th>
    <th width="120px"><?php _e( 'Reason', 'ADVERT_TEXTDOMAIN'); ?></th>
    <th width="120px"><?php _e( 'Timestamp', 'ADVERT_TEXTDOMAIN'); ?></th>
    </tr>
    </thead>
    <tbody style="text-align:center">

    <?php

    //$filter = new AdVert_Payments();
    $transaction_history = apply_filters('trans_adcredits', $post->ID, '10');    

    if( !empty($transaction_history)){  
        foreach( $transaction_history[0] as $transaction ){

            if ($transaction->removed > 0){
                echo '<tr class="ad-credit-removed"><td>'.number_format_i18n( str_replace("-", "", $transaction->removed), 2 ).'</td><td>'. __( 'Removed', 'ADVERT_TEXTDOMAIN') .'</td><td>'. esc_html($transaction->reason) .'</td><td>'. date_i18n( __( 'm/j/Y - G:i:s' ), strtotime($transaction->time) ) .'</td></tr>';
            }

            else{
                echo '<tr class="ad-credit-received"><td>'.number_format_i18n( str_replace("-", "", $transaction->added), 2 ).'</td><td>'. __( 'Added', 'ADVERT_TEXTDOMAIN') .'</td><td>'. esc_html($transaction->reason) .'</td><td>'. date_i18n( __( 'm/j/Y - G:i:s' ), strtotime($transaction->time) ) .'</td></tr>';
            }

        }
    }  

    ?>

    </tbody>
    </table>
    </div>
    </div>

    <?php

    $nonce = wp_create_nonce( 'advert-transactions-link' );
    echo '<p class="advert-transaction-link"><a href="'.admin_url('admin.php?page=advert-transactions&transpage=1&transadv='.$post->ID.'&_nonce='.$nonce).'">View More</a></p>';
        
}


//control the messages
function advertiser_updated_messages( $messages ) {

    global $post, $post_ID;

    $messages['advert-advertiser'] = array(
        0  => '', // Unused. Messages start at index 1.
        1  => __('Advertiser updated.' , 'ADVERT_TEXTDOMAIN') ,
        6  => __('Advertiser published.' , 'ADVERT_TEXTDOMAIN') ,
        8  => __('Advertiser submitted.' , 'ADVERT_TEXTDOMAIN'),
        9  => sprintf( __('Advertiser scheduled for: <strong>%1$s</strong>.' , 'ADVERT_TEXTDOMAIN'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
        10 =>  __('Advertiser draft updated.', 'ADVERT_TEXTDOMAIN')
    );

    return $messages;

}


//save post meta data
function advertiser_save_meta($post_id){

    if ('advert-advertiser' != get_post_type() || !current_user_can('publish_adverts'))
        return;

    if(!isset($_POST['advertiser_meta_box_nonce']) || !wp_verify_nonce($_POST['advertiser_meta_box_nonce'], 'advertiser_meta_box'))
        return;

    //justincase
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    //check the status
    $advertiser_status = get_post_status($post_id);

    if(isset($_POST['advertiser_company'])){

        $advertiser_company = sanitize_text_field(strip_tags($_POST['advertiser_company']));
        update_post_meta( $post_id, 'advertiser_company' , $advertiser_company );

        if( !wp_is_post_revision($post_id) ){

            remove_action('save_post', 'advertiser_save_meta');

            $update_advert = array(
                'ID'            => $post_id,
                'post_title'    => $advertiser_company,
                'post_name'     => preg_replace("/[\s_]/", "-", strip_tags($advertiser_company)),
            );

            wp_update_post( $update_advert );

            add_action('save_post', 'advertiser_save_meta');
        }

    }

    if(isset($_POST['advertiser_telephone'])){
        $advertiser_telephone = sanitize_text_field(strip_tags($_POST['advertiser_telephone']));
        update_post_meta( $post_id, 'advertiser_telephone' , $advertiser_telephone );
    }

    if(isset($_POST['advertiser_notes'])){
        $advertiser_notes = sanitize_text_field(strip_tags($_POST['advertiser_notes']));
        update_post_meta( $post_id, 'advertiser_notes' , $advertiser_notes );
    }

    if(isset($_POST['advertiser_email'])){
        if( is_email($_POST['advertiser_email'])){
            $advertiser_email = sanitize_email($_POST['advertiser_email']);
            update_post_meta( $post_id, 'advertiser_email' , $advertiser_email );
        }
    }

    //add or remove adcredits in array here
    if(isset( $_POST['add_credits'] ) && $_POST['add_credits'] !=''){

        $adcredits = number_format(sanitize_text_field($_POST['add_credits']), 2);
        $addremove = sanitize_text_field($_POST['add_remove_adcredits']);
        $reason    = sanitize_text_field($_POST['transaction_reason']); 

        if( preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $adcredits) && $adcredits > 0 ){
    
            if( $addremove === 'add' ){
                $transaction = apply_filters('add_adcredits', $post_id, $adcredits, '', $reason, '');    
            }
            elseif( $addremove === 'remove' ){
                $transaction = apply_filters('remove_adcredits', $post_id, $adcredits, '', $reason, '');   
            }

        if($transaction)
        do_action('calc_adcredits', $post_id);

        }

    }

    if(isset($_POST['advert_bypass_adcredits']) && current_user_can('manage_options')){
        update_post_meta( $post_id, 'advert_bypass_adcredits' , intval($_POST['advert_bypass_adcredits']) );
    }
    else{
        update_post_meta( $post_id, 'advert_bypass_adcredits' , 0 );    
    }

    $post_author_id = get_post_field('post_author', $post_id);

    if(isset($_POST['add_change_owner']) && current_user_can('publish_adverts')){

        $newOwner = sanitize_text_field($_POST['add_change_owner']);
        if( $newOwner != $post_author_id && !wp_is_post_revision($post_id) ){

            // unhook this function so it doesn't loop infinitely
            remove_action( 'save_post', 'advertiser_save_meta' );

            $update_advert = array(
                'ID'             => $post_id,
                'post_author'    => $newOwner,
            );

            wp_update_post( $update_advert );

            // re-hook this function
            add_action( 'save_post', 'advertiser_save_meta' );

            if(!user_can($newOwner, 'publish_adverts') || !user_can($newOwner, 'manage_options')){

                update_user_meta($newOwner, 'advert_advertiser_company_id'.get_current_blog_id(), $post_id);

                //add advertiser info to advert_user table
                global $wpdb;
                $table_name = $wpdb->prefix . 'advert_users';

                $wpdb->insert(
                    $table_name, 
		            array( 
			            'site_id' => get_current_blog_id(), 
			            'adv_id'  => $post_id,
                        'user_id' => $newOwner
		            ),
		            array( 
			            '%d', 
			            '%d', 
			            '%d'
		            ) 
                );

            }

        }

    }

    if(!empty($post_author_id) && $advertiser_status === 'publish'){

        if(!user_can( $post_author_id, 'publish_adverts') && !user_can($newOwner, 'manage_options') && user_can( $post_author_id, 'subscriber')){
            
            wp_update_user( array( 'ID' => $post_author_id, 'role' => 'advert_user' ) );
            update_user_meta($post_author_id, 'advert_advertiser_company_id'.get_current_blog_id(), $post_id);

            //add advertiser info to advert_user table
            global $wpdb;
            $table_name = $wpdb->prefix . 'advert_users';

            $wpdb->insert(
                $table_name, 
		        array( 
			        'site_id' => get_current_blog_id(), 
			        'adv_id'  => $post_id,
                    'user_id' => $post_author_id
		        ),
		        array( 
			        '%d', 
			        '%d', 
			        '%d'
		        ) 
            );

        }

    }

}//end save meta


function advertiser_delete_post($post_id){

    $post_type      = get_post_type( $post_id );
    $post_author_id = get_post_field('post_author', $post_id);

    if(!did_action('delete_post') && $post_type === 'advert-advertiser' && !user_can( $post_author_id, 'manage_options' ) && !user_can( $post_author_id, 'publish_adverts' )){
        
        if(user_can( $post_author_id, 'edit_adverts' )){
            wp_update_user( array( 'ID' => $post_author_id, 'role' => 'subscriber' ) );
            delete_user_meta( $post_author_id, 'advert_advertiser_company_id'.get_current_blog_id());

        }

    }

}



//add and change columns for advertisers
function custom_advertiser_columns($columns) {

    unset(
        $columns['title'],
        $columns['date']
    );

    $new_columns = array(
        'title'     => __('Title', 'ADVERT_TEXTDOMAIN'),
        'aid'       => __('AID', 'ADVERT_TEXTDOMAIN'),
        'owner'     => __('Owner', 'ADVERT_TEXTDOMAIN'),
        'email'     => __('Email', 'ADVERT_TEXTDOMAIN'),
        'adcredits' => __('Ad Credits', 'ADVERT_TEXTDOMAIN'),
        'campaigns' => __('Active Campaigns', 'ADVERT_TEXTDOMAIN'),
        'banners'   => __('# of Banners', 'ADVERT_TEXTDOMAIN'),
    );

    return array_merge($columns, $new_columns);

}



function custom_advertiser_column( $column, $post_id ) {

    switch ( $column ) {
        case 'title' :
        break;
        case 'aid' :
        echo $post_id; 
        break;
        case 'owner' :
        echo the_author_meta('user_login'); 
        break;
        case 'email' :
        echo get_post_meta($post_id, 'advertiser_email', true); 
        break;
        case 'adcredits' :
        $bypass = intval(get_post_meta($post_id, 'advert_bypass_adcredits', true));
        if($bypass === 1){echo 'n/a';}
        else{
        $company_credits = get_post_meta($post_id, 'company_credits', true);
        $company_credits = !empty($company_credits) ? number_format_i18n($company_credits, 2) : number_format_i18n(0, 2);
        echo $company_credits;
        }
        break;
        case 'campaigns' :
        $args = array('post_type' => 'advert-campaign', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_query' => array(array('key' => 'campaign_owner', 'value' => $post_id),));
        $posts = new WP_Query($args);
        $activeCount = 0;
        $today = strtotime(date('m/d/Y'));
        while($posts->have_posts()){
        $posts->the_post();
        $start = strtotime(get_post_meta($posts->post->ID, 'campaign_start_date', true));
        $stop  = strtotime(get_post_meta($posts->post->ID, 'campaign_stop_date', true));
        if ( $start <= $today && $stop >= $today || empty($start) && empty($stop) || $start <= $today && empty($stop) || empty($start) && $stop >= $today ){$activeCount = $activeCount + 1;}
        }
        echo number_format_i18n($activeCount);
        wp_reset_postdata();
        break;
        case 'banners' :
        $args = array('post_type' => 'advert-banner', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_query' => array(array('key' => 'banner_owner', 'value' => $post_id),));
        $posts = new WP_Query($args);
        echo number_format_i18n($posts->found_posts);
        wp_reset_postdata();
        break;
    }

}


function custom_advertiser_sortable_columns( $sortable_columns ) {

    $sortable_columns['aid']       = 'aid';
    $sortable_columns['owner']     = 'owner';
    $sortable_columns['company']   = 'company';
    $sortable_columns['email']     = 'email';
    $sortable_columns['adcredits'] = 'adcredits';
    $sortable_columns['campaigns'] = 'campaigns';
    $sortable_columns['banners']   = 'banners';
    return $sortable_columns;

}