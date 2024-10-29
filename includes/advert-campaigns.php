<?php

function campaign_start(){
    
    $campaign_owner = get_post_meta(get_the_ID(), 'campaign_owner', true);
    $bypass         = intval(get_post_meta($campaign_owner, 'advert_bypass_adcredits' , true));

    add_meta_box('advert_campaign', __( 'Campaign Owner', 'ADVERT_TEXTDOMAIN' ), 'campaign_meta_box', 'advert-campaign' , 'normal' , 'high' );
    add_meta_box('advert_campaign2', __( 'Campaign Options', 'ADVERT_TEXTDOMAIN' ), 'campaign_meta_box2', 'advert-campaign' , 'normal' , 'low' );
    
    if( $bypass != 1){
        add_meta_box('advert_campaign3', __( 'Campaign Overview', 'ADVERT_TEXTDOMAIN' ), 'campaign_meta_box3', 'advert-campaign' , 'side' , 'high' );
    }

    if(current_user_can('publish_adverts')){
        add_meta_box('advert_change_campaign_ownership', __( 'Change Control', 'ADVERT_TEXTDOMAIN' ), 'campaign_meta_box4', 'advert-campaign' , 'side' , 'low' );
    }

    remove_meta_box('postimagediv','advert-campaign','side');
    remove_meta_box( 'slugdiv', 'advert-campaign', 'normal' );
    remove_meta_box( 'submitdiv', 'advert-campaign', 'side' );

    add_meta_box('submitdiv', __( 'Publishing Tools', 'ADVERT_TEXTDOMAIN' ), 'advert_post_submit_meta_box', 'advert-campaign', 'side', 'high');

}


function campaign_meta_box($post){

    wp_nonce_field( 'campaign_meta_box', 'campaign_meta_box_nonce' );

    $advertisers    = get_posts(array('post_type' => 'advert-advertiser', 'posts_per_page' => -1, 'post_status' => 'publish'));
    $campaign_owner = get_post_meta($post->ID , 'campaign_owner' ,true);

    if( !current_user_can('publish_adverts') ){

        $user_id = get_current_user_id();
        $args = array('post_type' => 'advert-advertiser', 'author' => $user_id);
        $the_post = new WP_Query($args);
        $company = $the_post->posts[0]->ID;
        wp_reset_postdata();

        ?>

        <p>
        <select <?php if ( $campaign_owner ){echo 'disabled="disabled"';} ?> id="campaign_owner"  name="campaign_owner">
        <option value="<?php echo $the_post->posts[0]->ID;?>" selected="selected"><?php echo $the_post->posts[0]->post_title;?></option>
        </select>
        </p>

        <?php
        
    }
    else{

    ?>

        <p>
        <label for="campaign_owner"><strong><?php _e( 'Owner', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
        <span class="campaign_description"><?php _e( 'The advertiser for this campaign - This cannot be changed.', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
        <select <?php if ( $campaign_owner ){echo 'disabled="disabled"';} ?> id="campaign_owner"  name="campaign_owner">
        <option value=""><?php _e( 'None', 'ADVERT_TEXTDOMAIN' ); ?></option>
        <?php foreach($advertisers as $advertiser){?>
        <option value="<?php echo $advertiser->ID;?>" <?php selected($campaign_owner,$advertiser->ID);?> ><?php echo $advertiser->post_title;?></option>
        <?php } ?>
        </select>
        </p>

        <?php
        
    }

}


function campaign_meta_box2($post){
    
    global $advert_options;

    $locations              = get_posts(array('post_type' => 'advert-location', 'posts_per_page' => -1, 'post_status' => 'publish'));
    $banner_owners          = get_posts(array('post_type' => 'advert-banner', 'posts_per_page' => -1, 'post_status' => 'publish'));
    $campaign_budget        = get_post_meta($post->ID , 'campaign_budget' ,true);
    $campaign_budget_value  = get_post_meta($post->ID , 'campaign_budget_value' ,true);
    $campaign_location      = get_post_meta($post->ID , 'campaign_location' ,false);
    $pricing_model          = get_post_meta($post->ID , 'campaign_price_model' ,true);
    $campaign_owner         = get_post_meta($post->ID , 'campaign_owner' ,true);
    $campaign_start         = get_post_meta($post->ID , 'campaign_start_date' ,true);
    $campaign_stop          = get_post_meta($post->ID , 'campaign_stop_date' ,true);
    $campaign_priority      = get_post_meta($post->ID , 'campaign_priority' ,true);
    $campaign_impressions   = get_post_meta($post->ID , 'campaign_impressions' ,true);
    $campaign_ppimpressions = get_post_meta($post->ID , 'campaign_ppimpressions' ,true);
    $campaign_credits_avail = get_post_meta($campaign_owner, 'company_credits', true);
    $campaign_charges       = get_post_meta($post->ID, 'campaign_charges', true);
    $bypass                 = intval(get_post_meta($campaign_owner, 'advert_bypass_adcredits' , true));
    $campaign_status        = get_post_status($post->ID);

    //original amount set
    $campaign_budget_value_original = get_post_meta($post->ID , 'campaign_budget_value_original' ,true);

    if (!$campaign_owner && current_user_can('publish_adverts')){
        echo '<p class="campaign-notice">'. __( 'Once you select the Advertiser and either save draft or publish this campaign, you will have additional options specific to the advertiser.', 'ADVERT_TEXTDOMAIN' ) .'</p>'; 
        return;
    }
    elseif(!$campaign_owner && ! current_user_can('publish_adverts')){
        echo '<p class="campaign-notice">'. __( 'Once you enter a title for the campaign and save draft, you will have additional options.', 'ADVERT_TEXTDOMAIN' ) .'</p>';
        return;
    }

    if( $bypass != 1 ){

        $lessCharges = 0;
        if( $campaign_budget_value > 0 ){
            if( $campaign_budget === 'fixed' ){
                $log_fixed = apply_filters('fixed_campaign', $post->ID);
                $lessCharges = number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_fixed, 2), 2);
            }
            if( $campaign_budget === 'per_day' ){
                $log_perday = apply_filters('perday_campaign', $post->ID);
                $lessCharges = number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_perday, 2), 2);
            }
        }

        ?>

        <?php if( $post->post_status != 'publish' ) : ?>

        <p>
        <strong><?php _e( 'Once the campaign is submited, your location rate or rates will be locked in. View', 'ADVERT_TEXTDOMAIN' ); ?></strong>
        <span class="advert-open-rates-data hide-if-no-js">&nbsp;<a href="#"><?php _e('Current Rates', 'ADVERT_TEXTDOMAIN'); ?></a></span>
        </p>

        <?php 

        //display the current location rates
        do_action('advert_current_rates'); 
        
        endif;

        ?>  

        <p>
        <label><strong><?php _e( 'Budget', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
        <span class="campaign_description"><?php _e( 'Select budget type and how many AdCredits. Whenever the costs reach the budget value, the campaign will not display.', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
        <select id="campaign_budget" name="campaign_budget" <?php if ( $campaign_budget && $campaign_budget_value && $campaign_status === 'publish' ){echo 'disabled="disabled"';} ?> >
        <option value="fixed" <?php selected($campaign_budget,'fixed'); ?> ><?php _e( 'Fixed', 'ADVERT_TEXTDOMAIN' ); ?></option>
        <option value="per_day" <?php echo $pricing_model == 'cpp' ? 'disabled' : '';?> <?php selected($campaign_budget,'per_day');?> ><?php _e('Per Day', 'ADVERT_TEXTDOMAIN'); ?></option>
        </select>
        <input type="number" id="advert_price" step="0.01" min="0" max="<?php if (empty($campaign_credits_avail) || $campaign_credits_avail <= 0){echo '0';}else{echo $campaign_credits_avail;} ?>" class="campaign_budget_value" name="campaign_budget_value" placeholder="<?php _e('Budget' , 'ADVERT_TEXTDOMAIN');?>" value="<?php echo $lessCharges;?>" <?php echo 'required'; ?> />
        <span class="calcCampaign"><?php _e( 'AdCredits', 'ADVERT_TEXTDOMAIN' ); ?></span>
        <?php if (empty($campaign_credits_avail) || $campaign_credits_avail <= 0){echo '<span class="calcCampaign">&nbsp;&dash;&nbsp;'. __('No AdCredits Available', 'ADVERT_TEXTDOMAIN') .'</span>';} ?>
        </p>

        <?php
                
        $url = esc_url( admin_url( 'admin.php?page=advert-user#advert_user_dashboard2' ));
        if ( $campaign_credits_avail <= 0 && !current_user_can('publish_adverts') ){
            echo '<p class="campaign-notice">'. sprintf( __( 'No AdCredits available. Add more&nbsp;<a href="%s">AdCredits</a>', 'ADVERT_TEXTDOMAIN' ), $url) .'</a></p>';
        }

        if($campaign_budget_value_original){           
            echo sprintf( __('This campaign originally started with %s AdCredits', 'ADVERT_TEXT_DOMAIN'), $campaign_budget_value_original);
        }

        ?>

        <p>
        <label><strong><?php _e('Pricing Model', 'ADVERT_TEXTDOMAIN');?></strong></label><br />
        <span class="campaign_description"><?php _e('Select how you want this campaign to be charged.', 'ADVERT_TEXTDOMAIN');?></span><br />
                   
        <?php 
        
        //checks for allowable pricing models

        $cpc = 0;
        if( array_key_exists('advert_allow_pricing_model_users-CPC',$advert_options) ){
            $cpc = intval($advert_options['advert_allow_pricing_model_users-CPC']);
        }

        $cpm = 0;
        if( array_key_exists('advert_allow_pricing_model_users-CPM',$advert_options) ){
            $cpm = intval($advert_options['advert_allow_pricing_model_users-CPM']);
        }

        $cpp = 0;
        if( array_key_exists('advert_allow_pricing_model_users-CPP',$advert_options) ){
            $cpp = intval($advert_options['advert_allow_pricing_model_users-CPP']);
        }

        ?>

        <?php if( $cpc === 1 || current_user_can('publish_adverts') ) : ?>
        <label for="cpc"><input type="radio" class="campaign_price_model" name="campaign_price_model" <?php if($campaign_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="cpc" <?php checked($pricing_model , 'cpc');?> <?php if(empty($pricing_model)){echo 'checked';}?> /><?php _e( 'CPC - Cost per click', 'ADVERT_TEXTDOMAIN' ); ?></label><br />
        <?php endif; ?>

        <?php if( $cpm === 1 || current_user_can('publish_adverts') ) : ?>
        <label for="cpm"><input type="radio" class="campaign_price_model" name="campaign_price_model" <?php if($campaign_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="cpm" <?php checked($pricing_model , 'cpm');?> /><?php _e( 'CPM - Cost occurs every 1,000 impressions', 'ADVERT_TEXTDOMAIN' ); ?></label><br />
        <?php endif; ?>
                    
        <?php if( $cpp === 1 || current_user_can('publish_adverts') ) : ?>
        <label for="cpp"><input type="radio" class="campaign_price_model" name="campaign_price_model" <?php if($campaign_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="cpp" <?php checked($pricing_model , 'cpp');?> <?php echo $campaign_budget == 'per_day' ? 'disabled' : '';?> /><?php _e( 'CPP - Cost per period: An end date should be set if you choose this option', 'ADVERT_TEXTDOMAIN' ); ?></label>
        <?php endif; ?>
                    
        </p>

        <?php

    }//end check for bypass

    ?>

    <div class="location-select">
    <label for="locations"><strong><?php _e( 'Choose Locations', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <span class="campaign_description"><?php _e( 'Sets the location or locations where this campaign will run, if available', 'ADVERT_TEXTDOMAIN' ); ?></span><br />

    <?php 

    $banner_counter = 0;
    $loc_check = array();
    $loc_add = array();

    foreach($banner_owners as $banner_owner){

        if ( $campaign_owner === get_post_meta($banner_owner->ID , 'banner_owner' ,true) ){

            $banner_counter  = $banner_counter + 1;
            $banner_location = get_post_meta($banner_owner->ID , 'banner_location' ,true);
            $external_link    = get_post_meta($banner_owner->ID, 'banner_link', true);

            if( empty($external_link) ){
                $external_link = '&nbsp;-&nbsp;' . __( 'No Link Detected: At least one Banner for this location does not have a link', 'ADVERT_TEXTDOMAIN' );
            }
            else{
                $external_link = '';
            }

            if (!in_array($banner_location, $loc_check) ){

                ?>

                <div class="location-select-item">
                <label for="campaign_location[<?php echo $banner_location ?>]"><input type="checkbox" id="campaign_location[<?php echo $banner_location ?>]" name="campaign_location[<?php echo $banner_location ?>]" <?php if (in_array($banner_location, $campaign_location)) {echo "checked";} ?> value="<?php echo $banner_location ?>" /><?php echo get_the_title( $banner_location ) . $external_link;?></label>
                </div>

                <?php
                    
                array_push($loc_check, $banner_location);

            }
            else{
                array_push($loc_add, get_the_title($banner_location));
            }

        }

    }

    if ( !empty($loc_add) ){
        echo '<span class="campaign-notice">'. __( 'multiple Banners attached to', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;'.implode(', ', array_unique($loc_add)).'</span>';
    }

    $count_posts = wp_count_posts('advert-location');
    $url         = esc_url( admin_url( 'post-new.php?post_type=advert-banner' ));
    $url2        = esc_url( admin_url( 'post-new.php?post_type=advert-location' ));

    if ( $banner_counter < $count_posts->publish ){
        echo '<p class="campaign-notice">' . sprintf( __( 'Want to target more locations? Add more&nbsp;<a href="%s">Banners</a>', 'ADVERT_TEXTDOMAIN' ), $url) . '</p>';
    }
    if ($count_posts->publish === 0 && current_user_can('publish_adverts')){
        echo '<p class="campaign-notice">'. sprintf( __( 'No locations are set. Add more&nbsp;<a href="%s">Locations</a>', 'ADVERT_TEXTDOMAIN' ), $url2) .'</a></p>';
    }

    ?>
    </div>

    <p>
    <label for="campaign_start_date"><strong><?php _e( 'Schedule', 'ADVERT_TEXTDOMAIN' );?></strong></label><br />
    <span class="campaign_description"><?php _e( 'Set the start date for this campaign. The campaign will start on this date.', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <input type="text" name="campaign_start_date" class="adv_datetimepicker" id="campaign_start_date" <?php if($campaign_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="<?php echo $campaign_start;?>" placeholder="<?php _e('Start date - mm/dd/yyyy' , 'ADVERT_TEXTDOMAIN');?>" /><br /><br />
    <span class="campaign_description"><?php _e( 'Set the stop date for this campaign or leave empty. If left empty the campaign will run until the budget is depleted.', 'ADVERT_TEXTDOMAIN' ); ?></span><br />
    <input type="text" name="campaign_stop_date" class="adv_datetimepicker" id="campaign_stop_date" <?php if($campaign_status === 'publish' && ! current_user_can('publish_adverts')){echo 'disabled="disabled"';}?> value="<?php echo $campaign_stop;?>" placeholder="<?php _e('Stop date - mm/dd/yyyy' , 'ADVERT_TEXTDOMAIN');?>" />
    </p>

    <p>
    <label for="campaign_priority"><strong><?php _e( 'Priority', 'ADVERT_TEXTDOMAIN' );?></strong></strong></label><br />
    <span><?php _e( 'Greater numbers will display more often: 1 lowest | 10 Highest - Default is 5', 'ADVERT_TEXTDOMAIN' ); ?></strong></span><br />
    <select name="campaign_priority" id="campaign_priority">
    <option value="10" <?php if($campaign_priority === '10'){echo 'selected';} ?> >10</option>
    <option value="9" <?php if($campaign_priority === '9'){echo 'selected';} ?> >9</option>
    <option value="8" <?php if($campaign_priority === '8'){echo 'selected';} ?> >8</option>
    <option value="7" <?php if($campaign_priority === '7'){echo 'selected';} ?> >7</option>
    <option value="6" <?php if($campaign_priority === '6'){echo 'selected';} ?> >6</option>
    <option value="5" <?php if($campaign_priority === '5'){echo 'selected';}else if($campaign_priority === ''){echo 'selected';} ?> >5</option>
    <option value="4" <?php if($campaign_priority === '4'){echo 'selected';} ?> >4</option>
    <option value="3" <?php if($campaign_priority === '3'){echo 'selected';} ?> >3</option>
    <option value="2" <?php if($campaign_priority === '2'){echo 'selected';} ?> >2</option>
    <option value="1" <?php if($campaign_priority === '1'){echo 'selected';} ?> >1</option>
    </select>
    </p>

    <p>
    <label><strong><?php _e( 'Optional Limits', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <span><?php _e( 'Specify limits for this campaign', 'ADVERT_TEXTDOMAIN' ); ?></strong></span><br />
    </p>

    <p>
    <label for="campaign_impressions"><strong><?php _e( 'Total Impressions', 'ADVERT_TEXTDOMAIN' );?></strong></label><br />
    <input class="meta_campaign" id="campaign_impressions" type="number" min="0" step="100" value="<?php echo $campaign_impressions; ?>" name="campaign_impressions"/>
    </p>

    <p>
    <label for="campaign_ppimpressions"><strong><?php _e( 'Per Person Impressions', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
    <input class="meta_campaign" id="campaign_ppimpressions" type="number" min="0" step="10" value="<?php echo $campaign_ppimpressions; ?>" name="campaign_ppimpressions"/>
    </p>

    <?php
            
}


function campaign_meta_box3($post){

    $campaign_owner           = get_post_meta($post->ID , 'campaign_owner' ,true);
    $campaign_charges         = get_post_meta($post->ID , 'campaign_charges' ,true);
    $campaign_budget_value    = get_post_meta($post->ID , 'campaign_budget_value' ,true);
    $campaign_viewable_status = get_post_meta($post->ID , 'campaign_viewable_status' ,true);
    $campaign_budget          = get_post_meta($post->ID , 'campaign_budget' ,true);

    ?>

    <p>
    <label for="campaign_charges"><strong><?php _e( 'Campaign AdCredits Remaining', 'ADVERT_TEXTDOMAIN' ); ?></strong></label><br />
        
    <?php

    if( $campaign_budget_value > 0 ){    
        if( $campaign_budget === 'fixed' ){
            $log_fixed = apply_filters('fixed_campaign', $post->ID);
            echo '<h1>'. number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_fixed, 2), 2) .'</h1>';
        }
        if( $campaign_budget === 'per_day' ){
            $log_perday = apply_filters('perday_campaign', $post->ID);
            echo '<h1>'. number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_perday, 2), 2) .'</h1>';
        }
    }
    else{     
        echo '<h1>' . number_format_i18n(0, 2) . '</h1>';
    }

    ?>

    </p>

    <?php 

    if($post->post_status === 'publish'){
    
    ?>

        <p>
        <label for="campaign_viewable_status">Campaign is:&nbsp;
        <select id="campaign_viewable_status" name="campaign_viewable_status">
        <option value="active" <?php if($campaign_viewable_status === 'active'){echo 'selected';} ?> ><?php _e('Active', 'ADVERT_TEXTDOMAIN'); ?></option>
        <option value="paused" <?php if($campaign_viewable_status === 'paused'){echo 'selected';} ?> ><?php _e('Paused', 'ADVERT_TEXTDOMAIN'); ?></option>
        </select>
        </label>
        </p>

        <hr>

        <p class="advert-tip"><span class="advert-sm-info">AdVert Tip: Pausing the campaign will remove it from the selection pool.</span></p>

    <?php
    
    }
        
}


function campaign_meta_box4($post){

    $users = get_users();

    ?>

    <p>
    <span class="campaign_description"><?php _e( 'Useful when you manually create a new Campaign and want to attach it to a user on the site.', 'ADVERT_TEXTDOMAIN' ); ?></span><br /><br />
    <select id="campaign_change_owner"  name="campaign_change_owner">

    <?php 
 
    foreach($users as $user){

        if(user_can($user->ID, 'edit_adverts')){

            if(intval($post->post_author) === intval($user->ID)){
                echo '<option value="'.$user->ID.'" selected="selected">'.$user->user_login.'</option>';
            }
            else{
                echo '<option value="'.$user->ID.'">'.$user->user_login.'</option>';    
            }

        }

    } 

    ?>

    </select>
    </p>

    <p>
    Current Control: <?php if ( !user_can( $post->post_author, 'publish_adverts' )){echo _e('Advertiser', 'ADVERT_TEXTDOMAIN');}else{ _e('AdVert Manager', 'ADVERT_TEXTDOMAIN'); } ?>
    </p>

    <?php
            
}



//control the messages
function campaign_updated_messages( $messages ) {

    global $post, $post_ID;
    $messages['advert-campaign'] = array(
        0  => '', // Unused. Messages start at index 1.
        1  => __('Campaign updated.' , 'ADVERT_TEXTDOMAIN') ,
        6  => __('Campaign started.' , 'ADVERT_TEXTDOMAIN') ,
        8  => __('Campaign submitted.' , 'ADVERT_TEXTDOMAIN'),
        9  => sprintf( __('Campaign scheduled for: <strong>%1$s</strong>.' , 'ADVERT_TEXTDOMAIN'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
        10 =>  __('Campaign draft updated.', 'ADVERT_TEXTDOMAIN')
    );
    return $messages;

}


//save post meta data
function campaign_save_meta($post_id){

    if ( 'advert-campaign' != get_post_type() || !current_user_can('edit_adverts') )
        return;

    if(!isset($_POST['campaign_meta_box_nonce']) || !wp_verify_nonce($_POST['campaign_meta_box_nonce'], 'campaign_meta_box'))
        return;

    $campaign_status = get_post_status($post_id);

    //campaign_locked_rates
    if( $campaign_status == 'pending' && !current_user_can('publish_adverts') || $campaign_status == 'publish' ){  
        $lockrate = true;
    }
    else{
        $lockrate = false;
    }

    //justincase
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){return;}

    //double check the user
    $company_id = (isset($_POST['campaign_owner']) ? intval($_POST['campaign_owner']) : intval(get_post_meta($post_id, 'campaign_owner', true)));

    if(!current_user_can('publish_adverts')){
        $user_id    = get_current_user_id();
        $company_id = intval(apply_filters('get_advertiser_id', $user_id));
    }

    //check bypass
    $bypass = intval(get_post_meta($company_id, 'advert_bypass_adcredits' , true));

    //pause campaign
    $campaign_viewable_status = array('active', 'paused');
    if(isset($_POST['campaign_viewable_status'])){
        if(in_array($_POST['campaign_viewable_status'], $campaign_viewable_status)){
            update_post_meta( $post_id, 'campaign_viewable_status', $_POST['campaign_viewable_status'] );
        }
    }
    if(empty($_POST['campaign_viewable_status']) && get_post_status($post_id) === 'publish'){
        update_post_meta( $post_id, 'campaign_viewable_status', 'active' );
    }
    elseif(empty($_POST['campaign_viewable_status'])){
        update_post_meta( $post_id, 'campaign_viewable_status', 'inactive' );
    }

    if( !empty($_POST['campaign_budget'])){
        update_post_meta( $post_id, 'campaign_budget' , sanitize_text_field(strip_tags($_POST['campaign_budget'])) );
    }


    //adcredit amount/value
    $campaign_credits_avail = get_post_meta($company_id, 'company_credits', true);

    if( isset($_POST['campaign_budget_value']) && $_POST['campaign_budget_value'] <= $campaign_credits_avail && is_numeric($_POST['campaign_budget_value']) && $_POST['campaign_budget_value'] > 0 ){

        $campaign_budget_value  = number_format((float)get_post_meta($post_id, 'campaign_budget_value', true), 2);
        $campaign_budget        = get_post_meta($post_id, 'campaign_budget', true);

        if( $campaign_budget_value > 0 && !empty($campaign_budget_value) && !empty($campaign_budget) ){
            if( $campaign_budget === 'fixed' ){
                $log_fixed  = number_format(apply_filters('fixed_campaign', $post_id), 2);
                $new_campaign_budget_value = number_format(strip_tags($_POST['campaign_budget_value']), 2);               
                $new_campaign_budget_value = $new_campaign_budget_value >= ($campaign_budget_value - $log_fixed) ? $campaign_budget_value + ($new_campaign_budget_value - ($campaign_budget_value - $log_fixed)) : $campaign_budget_value - (($campaign_budget_value - $log_fixed) - $new_campaign_budget_value) ;

                if ( number_format($new_campaign_budget_value, 2) != number_format($campaign_budget_value, 2) ){
                    update_post_meta( $post_id, 'campaign_budget_value', $new_campaign_budget_value );                          
                }
                                 
            }
            elseif( $campaign_budget === 'per_day' ){
                update_post_meta( $post_id, 'campaign_budget_value' , number_format(strip_tags($_POST['campaign_budget_value']), 2) );  
            }          
        }
        else{
            update_post_meta( $post_id, 'campaign_budget_value' , number_format(strip_tags($_POST['campaign_budget_value']), 2) );
        }
    }

    if($campaign_status === 'publish' && !get_post_meta($post_id, 'campaign_budget_value_original' ,true) && $bypass != 1){
        update_post_meta( $post_id, 'campaign_budget_value_original', number_format(strip_tags($_POST['campaign_budget_value']), 2) );   
    }

    if( $campaign_status != 'publish' || current_user_can('publish_adverts')){

        if(!empty($company_id)){

            global $wpdb;
            $wpdb->update($wpdb->posts , array('post_parent' => $company_id) , array('ID' => $post_id) , array('%d'),array('%d'));
            update_post_meta( $post_id, 'campaign_owner' , sanitize_text_field(strip_tags($company_id)) );

        }

        //checks for allowable pricing models
        if(current_user_can('publish_adverts')){
            $pricing_array = array('cpc','cpm','cpp');
        }
        else{

            global $advert_options;
            $pricing_array = array();

            if( array_key_exists('advert_allow_pricing_model_users-CPC',$advert_options) ){
                $pricing_array[] = 'cpc';
            }

            $cpm = 0;
            if( array_key_exists('advert_allow_pricing_model_users-CPM',$advert_options) ){
                $pricing_array[] = 'cpm';
            }

            $cpp = 0;
            if( array_key_exists('advert_allow_pricing_model_users-CPP',$advert_options) ){
                $pricing_array[] = 'cpp';
            }
            
        }

        if(isset($_POST['campaign_price_model']) && in_array($_POST['campaign_price_model'], $pricing_array) ){
            update_post_meta( $post_id, 'campaign_price_model' , strip_tags($_POST['campaign_price_model']) );

            if( $_POST['campaign_price_model'] == 'cpp'){
                update_post_meta( $post_id, 'campaign_budget', 'fixed' );    
            }

        }

        if(isset($_POST['campaign_start_date'])){
            update_post_meta( $post_id, 'campaign_start_date', sanitize_text_field(strip_tags($_POST['campaign_start_date'])) );       
        }

        if(isset($_POST['campaign_stop_date'])){
            if( isset($_POST['campaign_start_date']) && isset($_POST['campaign_stop_date']) && $_POST['campaign_start_date'] <= $_POST['campaign_stop_date'] || empty($_POST['campaign_start_date']) ){
                update_post_meta( $post_id, 'campaign_stop_date', sanitize_text_field(strip_tags($_POST['campaign_stop_date'])) );
            }
        }

    }//if not published - limits users ability to change stuff

    if(isset($_POST['campaign_priority']) && is_numeric($_POST['campaign_priority'])){
        update_post_meta( $post_id, 'campaign_priority' , number_format($_POST['campaign_priority']) );
    }

    if(isset($_POST['campaign_impressions']) && is_numeric($_POST['campaign_impressions']) || empty($_POST['campaign_impressions'])){
        (empty($_POST['campaign_impressions']) ? update_post_meta($post_id, 'campaign_impressions', '') : update_post_meta($post_id, 'campaign_impressions', intval($_POST['campaign_impressions'])));
    }

    if(isset($_POST['campaign_ppimpressions']) && is_numeric($_POST['campaign_ppimpressions']) || empty($_POST['campaign_ppimpressions'])){
        (empty($_POST['campaign_ppimpressions']) ? update_post_meta($post_id, 'campaign_ppimpressions' , '') : update_post_meta($post_id, 'campaign_ppimpressions', intval($_POST['campaign_ppimpressions'])));
    }

    if(isset( $_POST['campaign_location'] )){
        delete_post_meta($post_id , 'campaign_location');

            if(is_array($_POST['campaign_location']) ){

                foreach( $_POST['campaign_location'] as $location ){
                    if(get_post_status($location)){
                        add_post_meta($post_id , 'campaign_location' , $location );
                    }
                    if($lockrate){

                        $lockrate_array = unserialize(get_post_meta($post_id, 'campaign_locked_rates', true));
                        $lockrate_array[$location] = get_post_meta($location, 'location_price', true);
                        update_post_meta($post_id , 'campaign_locked_rates' , serialize($lockrate_array) );
                    
                    }
                }

            }

    }

    if (isset($_POST['campaign_change_owner']) && current_user_can('publish_adverts') ){

    $post_author_id = get_post_field('post_author', $post_id);
    $newOwner = intval(strip_tags($_POST['campaign_change_owner']));

    if ( $newOwner != $post_author_id && !wp_is_post_revision($post_id)){

        // unhook this function so it doesn't loop infinitely
        remove_action( 'save_post', 'campaign_save_meta' );

        $update_campaign = array(
            'ID'            => $post_id,
            'post_author'   => $newOwner,
        );

        wp_update_post( $update_campaign );

        // re-hook this function
        add_action( 'save_post', 'campaign_save_meta' );

    }

    }


}//save meta



//add and change columns for campaigns
function custom_campaign_columns($columns) {

    unset(
        $columns['title'],
        $columns['date']
    );

    $new_columns = array(
        'title'      => __('Title', 'ADVERT_TEXTDOMAIN'),
        'cid'        => __('CID', 'ADVERT_TEXTDOMAIN'),
        'owner'      => __( 'Owner', 'ADVERT_TEXTDOMAIN' ),
        'budget'     => __( 'Type', 'ADVERT_TEXTDOMAIN' ),
        'amount'     => __( 'Amount', 'ADVERT_TEXTDOMAIN' ),
        'available'  => __( 'Available', 'ADVERT_TEXTDOMAIN' ),
        'location'   => __( 'Location(s)', 'ADVERT_TEXTDOMAIN' ),
        'start_date' => __( 'Start Date', 'ADVERT_TEXTDOMAIN' ),
        'stop_date'  => __( 'Stop Date', 'ADVERT_TEXTDOMAIN' ),
        'status'     => __( 'Status', 'ADVERT_TEXTDOMAIN' ),
    );

    return array_merge($columns, $new_columns);

}



function custom_campaign_column( $column, $post_id ) {

    switch ( $column ) {
        case 'title' :
        break;
        case 'cid' :
        echo $post_id; 
        break;
        case 'owner' :
        if ( get_post_meta($post_id, 'campaign_owner', true) ){echo get_the_title( get_post_meta( $post_id , 'campaign_owner' , true ) );}
        break;
        case 'budget' :
        $bypass = intval(get_post_meta(get_post_meta($post_id, 'campaign_owner', true), 'advert_bypass_adcredits' , true));
        if($bypass === 1){echo 'n/a';}
        else{
        $campaign_budget = get_post_meta( $post_id , 'campaign_budget' , true );
        if( $campaign_budget === 'fixed' ){
            _e( 'Fixed', 'ADVERT_TEXTDOMAIN' );
        }
        elseif( $campaign_budget === 'per_day' ){
            _e( 'Per Day', 'ADVERT_TEXTDOMAIN' );       
        }
        }
        break;
        case 'amount':
        $bypass = intval(get_post_meta(get_post_meta($post_id, 'campaign_owner', true), 'advert_bypass_adcredits', true));
        $budget_value = get_post_meta($post_id, 'campaign_budget_value', true);
        if($bypass === 1){echo 'n/a';}
        elseif(!empty($budget_value)){echo number_format_i18n($budget_value, 2);} 
        break;
        case 'available':
        $bypass = intval(get_post_meta(get_post_meta($post_id, 'campaign_owner', true), 'advert_bypass_adcredits' , true));
        //$campaign_charges       = get_post_meta($post_id, 'campaign_charges', true);
        $campaign_budget        = get_post_meta( $post_id , 'campaign_budget' , true );
        $campaign_budget_value = get_post_meta($post_id, 'campaign_budget_value', true);
        if($bypass === 1){
            echo 'n/a';
        }
        elseif( $campaign_budget_value > 0 ){    
            if( $campaign_budget === 'fixed' ){
                $log_fixed = apply_filters('fixed_campaign', $post_id);
                echo number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_fixed, 2), 2);
            }
            if( $campaign_budget === 'per_day' ){
                $log_perday = apply_filters('perday_campaign', $post_id);
                echo number_format_i18n(number_format($campaign_budget_value, 2) - number_format($log_perday, 2), 2);
            }
        }
        else{     
            echo number_format_i18n(0, 2);
        }
        break;
        case 'location':
        $loc_array = get_post_meta($post_id, 'campaign_location', false);
        echo number_format_i18n(count($loc_array)); 
        break;
        case 'start_date':
        $startdate = get_post_meta($post_id, 'campaign_start_date', true );
        echo !empty($startdate) ? date_i18n( __( 'm/j/Y' ), strtotime($startdate)) : ''; 
        break;
        case 'stop_date':
        $stopdate = get_post_meta($post_id, 'campaign_stop_date', true );
        echo !empty($stopdate) ? date_i18n( __( 'm/j/Y' ), strtotime($stopdate)) : '';
        break;
        case 'status':
        echo get_post_meta($post_id, 'campaign_viewable_status', true ); 
        break;
    }

}


function campaign_sortable_columns( $sortable_columns ) {

    $sortable_columns['cid']        = 'cid';
    $sortable_columns['owner']      = 'owner';
    $sortable_columns['budget']     = 'budget';
    $sortable_columns['amount']     = 'amount';
    $sortable_columns['available']  = 'available';
    $sortable_columns['location']   = 'location';
    $sortable_columns['start_date'] = 'start_date';
    $sortable_columns['stop_date']  = 'stop_date';
    $sortable_columns['status']     = 'status';
    return $sortable_columns;

}