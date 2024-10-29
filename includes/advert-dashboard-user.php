<?php
/**
* AdVert User Dashboard
*
* Displays information and other options to the Advertiser/User (not admin or AdVert Manager)
*
* @since 1.0.0
*
* @package AdVert
* @package advert\advert-user-dashboard
*/

global $howmany;
global $post_id;
global $user_id;


add_action('advert_init', 'advert_user_dashboard_add_meta_box');
add_action('advert_init', 'add_new_advertiser');
add_action('advert_init', 'do_advert_user_dashboard');


function advert_dashboard_user_start() {

    do_action('advert_init');

}


//add the boxes
function advert_user_dashboard_add_meta_box(){

    global $howmany;
    global $post_id;
    global $user_id;

    //who are we working with
    $user_id = intval(get_current_user_id());
    $howmany = intval(apply_filters('check_advertiser', $user_id));
    $post_id = intval(apply_filters('get_advertiser_id', $user_id));

    //global $wp_registered_widgets, $wp_registered_widget_controls;

    require_once( ABSPATH . '/wp-admin/includes/screen.php' );

    $currentScreen = get_current_screen();

    advert_add_dashboard_user_widget( 'advert_user_dashboard1', __( 'Your Information', 'ADVERT_TEXTDOMAIN' ), 'advert_user_dashboard_meta_box1' );

    if($howmany > 0){
    advert_add_dashboard_user_widget( 'advert_user_dashboard2', __( 'AdCredits', 'ADVERT_TEXTDOMAIN' ), 'advert_user_dashboard_meta_box2' );
    advert_add_dashboard_user_widget( 'advert_user_dashboard3', __( 'Transaction History', 'ADVERT_TEXTDOMAIN' ), 'advert_user_dashboard_meta_box3' );
    }

    do_action( 'do_meta_boxes', $currentScreen->id, 'normal', '' );
    do_action( 'do_meta_boxes', $currentScreen->id, 'side', '' );

    echo '<div id="advert-dashboard-meta-prefs-workaround" class="hide-if-no-js"><h5>'. __( 'Show on screen', 'ADVERT_TEXTDOMAIN' ) .'</h5>';
    meta_box_prefs($currentScreen);

    if ( $howmany > 0 ){
        // 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
        if ( isset( $_GET['welcome'] ) ) {
		    $welcome_checked = empty( $_GET['welcome'] ) ? 0 : 1;
		    update_user_meta( get_current_user_id(), 'show_advert_welcome_panel', $welcome_checked );
	    } 
        else {
	        $welcome_checked = get_user_meta( get_current_user_id(), 'show_advert_welcome_panel', true );
        }

	    echo '<label for="advert_welcome_panel-hide">';
	    echo '<input type="checkbox" id="advert_welcome_panel-hide"' . checked( (bool) $welcome_checked, true, false ) . ' />';
	    echo __( 'Welcome', 'ADVERT_TEXTDOMAIN' ) . "</label>\n";
    }

    echo '</div>';

}


function advert_user_dashboard_meta_box1(){

    global $advert_options;
    global $howmany;
    global $post_id;
    global $user_id;

    ?>

    <form id="new-advertiser" name="new_advertiser" method="post" action="">

    <?php 
    if(!empty($post_id) && get_post_status($post_id) === 'publish' && current_user_can( 'edit_adverts' ) ){
    echo '<div class="user-status active"><p>'. __( 'Active', 'ADVERT_TEXTDOMAIN' ) .'</p></div>';
    }
    else{
    echo '<div class="user-status inactive"><p>'. __( 'Inactive', 'ADVERT_TEXTDOMAIN' ) .'</p></div>';
    }
    ?>

    <?php wp_nonce_field( 'add_advertiser', 'user_advertiser' ); ?>
    <?php wp_get_referer() ?>
    <input type="hidden" id="user-id" name="user_id" value="<?php echo $user_id; ?>" />
    <input type="hidden" id="hiddenaction" name="action" value="addadvertiser" />
    <input type="hidden" id="originalaction" name="originalaction" value="addadvertiser" />
    <input type="hidden" id="post_author" name="post_author" value="<?php echo $user_id; ?>" />
    <input type="hidden" id="post_type" name="post_type" value="advertiser" />
    <input type="hidden" name="action" value="post" />

    <table class="form-table">
    <tbody>

    <tr>
    <th>
    <label for="user-company"><?php _e( 'Name (Company or Title)', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </th>
    <td>
    <input type="text" name="advertiser_name" id="user-company" value="<?php if(!empty($post_id)){echo get_post_meta($post_id, 'advertiser_company', true);}elseif(!empty($_POST['advertiser_name'])){echo $_POST['advertiser_name'];} ?>" required>
    </td>
    </tr>

    <tr>
    <th>
    <label for="user-email"><?php _e( 'Email Address', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </th>
    <td>
    <input type="email" name="advertiser_email" id="user-email" value="<?php if(!empty($post_id)){echo get_post_meta($post_id, 'advertiser_email', true);}elseif(!empty($_POST['advertiser_email'])){echo $_POST['advertiser_email'];} ?>" required>
    </td>
    </tr>

    <tr>
    <th>
    <label for="user-phone"><?php _e( 'Telephone Number', 'ADVERT_TEXTDOMAIN' ); ?></label>
    </th>
    <td>
    <input type="tel" name="advertiser_phone" id="user-phone" value="<?php if(!empty($post_id)){echo get_post_meta($post_id, 'advertiser_telephone', true);}elseif(!empty($_POST['advertiser_phone'])){echo $_POST['advertiser_phone'];} ?>" required>
    </td>
    </tr>

    </tbody>
    </table>

    <?php if (!empty($post_id)){?>
    <p class="submit advert-user-button"><input id="submit" name="save" type="submit" class="button button-primary button-large" value="<?php _e( 'Update Info', 'ADVERT_TEXTDOMAIN'); ?>" /></p>
    <?php } else{?>
    <p class="submit advert-user-button"><input id="submit" name="save" type="submit" class="button button-primary button-large" value="<?php _e( 'Add Info', 'ADVERT_TEXTDOMAIN'); ?>" /></p>

    <?php  



    $newText = '';
    if( array_key_exists('advert_display_text_to_reg_users',$advert_options) ){
    $newText = $advert_options['advert_display_text_to_reg_users'];
    }

    echo '<p class="advert-tip"><span class="advert-sm-info">'.$newText.'</span></p>';
    } 
    ?>

    </form>

    <?php

}




function advert_user_dashboard_meta_box2(){

    global $advert_options;
    global $howmany;
    global $post_id;
    global $user_id;

    if( $howmany === 1 ){

        echo '<p>' . __( 'AdCredits Available', 'ADVERT_TEXTDOMAIN') . '</p>';
      
        $company_credits = get_post_meta($post_id, 'company_credits', true);

        if(empty($company_credits)){
            echo '<h1>0</h1>';
        }
        else{
            echo '<h1>'.number_format_i18n( $company_credits, 2).'</h1>';
        }
 
        if(has_action('advert_payment_type')){

            if(has_action('advert_lightbox')){
                do_action('advert_lightbox');
            }

            echo '<noscript>' . __( 'Enable JavaScript to add AdCredits', 'ADVERT_TEXTDOMAIN') . '</noscript>';
            echo '<p><a class="advert-show-add-funds" href="#">' . __( 'Add more AdCredits', 'ADVERT_TEXTDOMAIN') . '</a></p>';
            echo '<div class="advert-add-funds">';

            do_action('advert_payment_type'); 

            echo '</div>';

        }
        
    }

}



function advert_user_dashboard_meta_box3(){

    global $howmany;
    global $post_id;
    global $user_id;

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
    $transaction_history = apply_filters('trans_adcredits', $post_id, '10');    

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
        
    echo '<p class="advert-transaction-link"><a href="'.admin_url('admin.php?page=advert-transactions').'">View More</a></p>';

}



function advert_add_dashboard_user_widget($widget_id, $widget_name, $callback, $control_callback = null, $callback_args = null) {
	$screen = get_current_screen();

	$side_widgets = array('advert_user_dashboard3');

	$location = 'normal';
	if (in_array($widget_id, $side_widgets))
		$location = 'side';

	$priority = 'core';
	if ('dashboard_browser_nag' === $widget_id)
		$priority = 'high';
        
	add_meta_box($widget_id, $widget_name, $callback, $screen, $location, $priority, $callback_args);
}


function add_new_advertiser(){

    global $advert_options;
    global $howmany;
    global $post_id;
    global $user_id;

    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'post'){
    if ( !is_user_logged_in() || ! wp_verify_nonce( $_POST['user_advertiser'], 'add_advertiser' ) ){
    print 'Woah, whats really going on...?';
    return;
    }
 
    $user_id	      = $_POST['user_id'];
    $current_user_id  = $user_id;
    $post_title       = sanitize_text_field( $_POST['advertiser_name'] );
    $email		      = sanitize_email( $_POST['advertiser_email'] );
    $phone		      = sanitize_text_field( $_POST['advertiser_phone'] );

    if ( $user_id != $current_user_id )
        return;

    global $notice_array;
    global $notice_num;
    $notice_array = array();

    if (empty($post_title)) $notice_array[] = __( 'Please add your Company name or Title.', 'ADVERT_TEXTDOMAIN');
    if (empty($email)) $notice_array[] = __( 'Please add an email address.', 'ADVERT_TEXTDOMAIN');
    if (!is_email($email)) $notice_array[] = __( 'Please use a different email address.', 'ADVERT_TEXTDOMAIN');
    if (empty($phone)) $notice_array[] = __( 'Please add a telephone number.', 'ADVERT_TEXTDOMAIN');

    if (count($notice_array) > 0){
    $notice_num = 0;
    add_action('advert-notices', 'advert_notices');
    return;
    }

    if ( $howmany === 0 && empty($post_id) ){

    // Create post object
    $add_advert = array(
        'post_title'    => $post_title,
        'post_status'   => 'pending',
        'post_author'   => $user_id,
        'post_type'     => 'advert-advertiser',
    );

    // Insert the post into the database
    $post_id = wp_insert_post( $add_advert );
    $notice_array[] = __( 'Your information has been submitted for approval.', 'ADVERT_TEXTDOMAIN');
    $notice_num = 1;
    add_action('advert-notices', 'advert_notices');
    }
    else{
    if(!empty($post_id)){
    $update_advert = array(
        'ID'            => $post_id,
        'post_title'    => $post_title,
    );
    wp_update_post( $update_advert );
    if(!empty($post_id) && $howmany === 0){$notice_array[] = __( 'Your information has been updated and waiting approval.', 'ADVERT_TEXTDOMAIN');}
    else{$notice_array[] = __( 'Your information has been updated.', 'ADVERT_TEXTDOMAIN');}
    $notice_num = 1;
    add_action('advert-notices', 'advert_notices');
    }
    }

    if(!empty($post_id)){
    update_post_meta($post_id, 'advertiser_company', $post_title);
    update_post_meta($post_id, 'advertiser_email', $email);
    update_post_meta($post_id, 'advertiser_telephone', $phone);
    update_user_meta( $user_id, 'advert_advertiser_company_id'.get_current_blog_id(), $post_id);
    }

    }

}



/** Create the User Dashboard */
function do_advert_user_dashboard(){

    $currentScreen = get_current_screen();
    global $advert_options;
    global $howmany;
    global $post_id;
    global $user_id;

    $columns = absint( $currentScreen->get_columns() );
    $columns_css = '';
    if ( $columns ) {
        $columns_css = " columns-$columns";
    }

    wp_enqueue_style('dashboard');
    wp_enqueue_script( 'dashboard' );

    echo '<div class="wrap">';
    echo '<div class="advert-page-heading-logo">a</div>';
    echo '<h1 class="advert-heading-tag">'. __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ) .'</h1>';
    echo '<h2 class="dummy-h2"></h2>';

    //display notices
    do_action('advert-notices');


    //check if advertiser is published
    if ( $howmany > 0 ){

    //welcome
    $classes = 'welcome-panel';

    //get options
    $option = intval(get_user_meta( get_current_user_id(), 'show_advert_welcome_panel', true ));

    if($option != 1 && $option != 0){
        $option = 1;
    }

    $user_analysis = 0;
    if(array_key_exists('advert_allow_analysis_users', $advert_options)){
    $user_analysis = intval($advert_options['advert_allow_analysis_users']);
    }

    // 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
    //$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
    $hide = 0 == $option;
    if ( $hide )
	    $classes .= ' hidden';

    ?>
    <div id="advert-welcome-panel" class="<?php echo $classes; ?>">
        <?php wp_nonce_field( 'advert-welcome-panel-nonce', 'advertwelcomepanelnonce', false ); ?>		
        <a class="advert-welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>"><?php _e( 'Dismiss', 'ADVERT_TEXTDOMAIN' ); ?></a>
	    <div class="welcome-panel-content">
	    <h3><?php _e( 'Welcome', 'ADVERT_TEXTDOMAIN' ); echo '&nbsp;'.wp_get_current_user()->user_login; ?></h3>
	    <p class="about-description"><?php _e( 'We have assembled some links and information to get you started', 'ADVERT_TEXTDOMAIN' ); ?>:</p>
	    <div class="welcome-panel-column-container">
	    <div class="welcome-panel-column">
	    <h4><?php _e( 'Getting Started', 'ADVERT_TEXTDOMAIN' ); ?></h4>
        <p><?php _e( 'Ensure "Your Information" is correct below and:', 'ADVERT_TEXTDOMAIN' ); ?></p>
        <ul>
	    <li><a href="<?php echo esc_url(admin_url( 'profile.php' )); ?>" class="welcome-icon welcome-write-blog"><?php _e( 'Update your profile', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
        </ul>
        </div>
	    <div class="welcome-panel-column">
        <h4><?php _e( 'Next Steps', 'ADVERT_TEXTDOMAIN' ); ?></h4>
        <ul>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-banner' )); ?>" class="welcome-icon welcome-write-blog"><?php _e( 'Add a new Banner', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-campaign' )); ?>" class="welcome-icon welcome-write-blog"><?php _e( 'Add a new Campaign', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    </ul>
	    </div>
	    <div class="welcome-panel-column welcome-panel-last">
	    <h4><?php _e( 'More Actions', 'ADVERT_TEXTDOMAIN' ); ?></h4>
	    <ul>
        <?php if( $user_analysis === 1 ) { ?>
        <li><a href="<?php echo esc_url(admin_url( 'admin.php?page=advert-analysis-overview' )); ?>" class="welcome-icon welcome-view-site"><?php _e( 'View Analysis', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    <?php } ?>
        <?php if(has_action('advert_payment_type')){ ?>
        <li><a href="#advert_user_dashboard2" class="welcome-icon welcome-add-page"><?php _e( 'Add AdCredits', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
        <?php } ?>
        <li><a href="#advert_user_dashboard3" class="welcome-icon welcome-view-site"><?php _e( 'View Transactions', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    <li><a href="https://norths.co/advert/documentation/" class="welcome-icon welcome-learn-more"><?php _e( 'Learn more about AdVert', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
        </ul>
	    </div>
	    </div>
	    </div>
	    </div>
    <?php

    }


    echo '<div id="dashboard-widgets-wrap">';
    echo '<div id="dashboard-widgets" class="metabox-holder'.$columns_css.'">';
    echo '<div id="postbox-container-1" class="postbox-container">';
    do_meta_boxes( $currentScreen->id, 'normal', '' );
    echo '</div>';

    echo '<div id="postbox-container-2" class="postbox-container">';
    do_meta_boxes( $currentScreen->id, 'side', '' );
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

}