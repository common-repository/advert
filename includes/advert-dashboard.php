<?php

add_action('advert_init', 'checkPostData');
add_action('advert_init', 'advert_dashboard_add_meta_box');
add_action('advert_init', 'do_advert_dashboard');


function advert_dashboard_start() {

    do_action('advert_init');

}


//add the boxes
function advert_dashboard_add_meta_box(){

    global $wp_registered_widgets, $wp_registered_widget_controls;

    $currentScreen = get_current_screen();

    //wp_add_dashboard_widget( 'advert_dashboard_welcome', __( 'AdVert Welcome', 'ADVERT_TEXTDOMAIN' ), 'advert_dashboard_welcome_meta_box' );
    advert_add_dashboard_widget( 'advert_dashboard1', __( 'AdVert News', 'ADVERT_TEXTDOMAIN' ), 'advert_dashboard_meta_box1' );
    advert_add_dashboard_widget( 'advert_dashboard2', __( 'Send Suggestions', 'ADVERT_TEXTDOMAIN' ), 'advert_dashboard_meta_box2' );
    advert_add_dashboard_widget( 'advert_dashboard3', __( 'Stay in touch', 'ADVERT_TEXTDOMAIN' ), 'advert_dashboard_meta_box3' );
    advert_add_dashboard_widget( 'advert_dashboard4', __( 'Whats new', 'ADVERT_TEXTDOMAIN' ), 'advert_dashboard_meta_box4' );

    do_action( 'do_meta_boxes', $currentScreen->id, 'normal', '' );
    do_action( 'do_meta_boxes', $currentScreen->id, 'side', '' );

    echo '<div id="advert-dashboard-meta-prefs-workaround" class="hide-if-no-js"><h5>Show on screen</h5>';
    meta_box_prefs($currentScreen);

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

    echo '</div>';

}



function advert_dashboard_meta_box1(){

    $rss1 = fetch_feed( 'https://norths.co/advert/feed/' );
    $maxitems = 0;

    if ( ! is_wp_error( $rss1 ) ) { // Checks that the object is created correctly

        // Figure out how many total items there are, but limit it to 5. 
        $maxitems = $rss1->get_item_quantity( 5 ); 

        // Build an array of all the items, starting with element 0 (first element).
        $rss1_items = $rss1->get_items( 0, $maxitems );

    }

    ?>

    <ul>
        <?php if ( $maxitems == 0 ) : ?>
            <li><?php _e( 'No items', 'ADVERT_TEXTDOMAIN' ); ?></li>
        <?php else : ?>
            <?php $firstfeed = 1; ?>
            <?php foreach ( $rss1_items as $item ) : ?>
                <li>
                    <a class="rsswidget" href="<?php echo esc_url( $item->get_permalink() ); ?>"
                        title="<?php echo esc_html( $item->get_title() ); ?>">
                        <?php echo esc_html( $item->get_title() ); ?>
                    </a>          
                    <span>&nbsp;-&nbsp;&nbsp;<?php printf( $item->get_date('F j Y') ); ?></span>
                    <?php if($firstfeed === 1){echo '<div class="rssSummary">'.strip_tags($item->get_description(), '<p>').'</div><hr>';}$firstfeed = 2; ?>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <?php

}


function advert_dashboard_meta_box2(){

    ?>

    <form style="text-align:center;" action="" method="post">
    <input type="hidden" id="post_type" name="post_type" value="admin_dashboard_action" />
    <input type="hidden" id="originalaction" name="originalaction" value="sendsuggestion" />
    <?php wp_nonce_field( 'admin_dashboard', 'admin-dashboard' ); ?>
    <?php wp_get_referer() ?>
    <textarea id="advert_suggestion_content" name="advert_suggestion_content" class="advert-suggestions" rows="7" required></textarea>
    <label for="advert_send_identifiable_data"><input id="advert_send_identifiable_data" name="advert_send_identifiable_data" type="checkbox" value="Yes"><?php _e( 'Send your site URL and email address for correspondence?', 'ADVERT_TEXTDOMAIN' ); ?></label>
    <br />
    <input id="advert-suggestion-submit" class="button button-primary button-large" type="submit" value="Send">
    <br />
    <span class="advert-sm-info"><?php _e( 'Optionally, you can visit the AdVert site', 'ADVERT_TEXTDOMAIN' ); ?>&nbsp;<a href="https://norths.co/advert/support/"><?php _e( 'here', 'ADVERT_TEXTDOMAIN' ); ?></a></span>
    </form>

    <?php
        
}

function advert_dashboard_meta_box3(){

    ?>

    <form style="text-align:center;" action="https://norths.co/info-handler/advert-log-emails.php" method="POST">
    <input type="hidden" name="originalaction" value="advert_add_email">
    <div>
    <p><?php echo _e( 'Get the latest news, tips and updates from AdVert for WordPress.', 'ADVERT_TEXTDOMAIN' ); ?></p>
    <div style="margin:10px 0 10px 0;"><label for="mce-EMAIL" style="display: block;margin-bottom: 3px;">Email Address</label>
    <input style="font-size:22px;text-align:center;max-width:100%;" type="email" value="" name="email" class="required email" id="mce-EMAIL" placeholder="email@domain.com" required>
    <input id="advert-mailer-submit" class="button button-primary button-large" type="submit" value="Subscribe Now">
    </div>
    <p><span class="advert-sm-info"><?php _e( 'Your email will not be shared with anyone', 'ADVERT_TEXTDOMAIN' ); ?></span></p>
    </div>
    </form>

    <hr>

    <div id="advert-social-follow-me">
        <p>Follow AdVert</p>
        <a href="https://twitter.com/northsco" target="_blank"><span class="dashicons dashicons-twitter"></span></a>
        <a href="https://www.facebook.com/northsco.advert" target="_blank"><span class="dashicons dashicons-facebook"></span></a>
        <a href="https://plus.google.com/u/0/b/102098601360016593459/102098601360016593459/posts" target="_blank"><span class="dashicons dashicons-googleplus"></span></a>
    </div>

    <?php
        
}



function advert_dashboard_meta_box4(){

    $changelog = file_get_contents(  ADVERT_PLUGIN_DIR . 'changelog.txt' );
    echo $changelog;

}



function advert_add_dashboard_widget( $widget_id, $widget_name, $callback, $control_callback = null, $callback_args = null ) {

	$screen = get_current_screen();

	$side_widgets = array( 'advert_dashboard2', 'advert_dashboard4' );

	$location = 'normal';
	if ( in_array($widget_id, $side_widgets) )
		$location = 'side';

	$priority = 'core';
	if ( 'dashboard_browser_nag' === $widget_id )
		$priority = 'high';

	add_meta_box( $widget_id, $widget_name, $callback, $screen, $location, $priority, $callback_args );
}



function checkPostData(){

    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && current_user_can('publish_adverts') ){

        if ( !is_user_logged_in() || ! wp_verify_nonce( $_POST['admin-dashboard'], 'admin_dashboard' ) ){
        print 'Woah, whats really going on...?';
        return;
        }

        global $notice_array;
        global $notice_num;
        $notice_array = array();

        if($_POST['originalaction'] == 'sendsuggestion'){

            $version   = '';
            $dbversion = '';
            $version   = new AdVert_For_Wordpress();
            $version   = $version->get_advert_version();
            $dbversion = get_option('advert_db_version');
    
            if( !empty( $_POST['advert_suggestion_content']) && isset($_POST['advert_send_identifiable_data']) && $_POST['advert_send_identifiable_data'] === 'Yes' ){

                $siteurl = esc_url(get_site_url());
                $curUser = wp_get_current_user();
                $message = 'AdVert Version:'.$version.'<br />AdVert DB Version:'.$dbversion.'<br />Site: '.$siteurl.'<br />Email Address: '.$curUser->user_email.'<br /><br />'.strip_tags ($_POST['advert_suggestion_content'], '<br><br />');
                $headers = 'From: '.$curUser->display_name.' <'.$curUser->user_email.'>' . "\r\n";

                do_action('advert_send_emails','advert.support@norths.co','AdVert Suggestions/Feedback',$message,$headers);  

                $notice_num = 1;
                $notice_array[]='Your suggestions have been sent, Thank You.'; 

            }
            elseif(!empty($_POST['advert_suggestion_content'])){

                $message = 'AdVert Version:'.$version.'<br />AdVert DB Version:'.$dbversion.'<br /><br />From an AdVert user<br /><br />'.strip_tags ($_POST['advert_suggestion_content'], '<br><br />');

                do_action('advert_send_emails','advert.support@norths.co','AdVert Suggestions/Feedback',$message,''); 

                $notice_num = 1;
                $notice_array[] = __( 'Your suggestions have been sent, Thank You.', 'ADVERT_TEXTDOMAIN');   

            }
            else{
                $notice_num = 0;
                $notice_array[] = __( 'Please add your suggestion(s) before submitting.', 'ADVERT_TEXTDOMAIN');  
            }
    
        }

        //add notices
        if (count($notice_array) > 0){
            add_action('advert-notices', 'advert_notices');
            return;
        }

    }//end if post

}//check post data




function do_advert_dashboard(){

    $currentScreen = get_current_screen();

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

    //display notices
    do_action('advert-notices');


    //welcome
    $classes = 'welcome-panel';

    $option = get_user_meta( get_current_user_id(), 'show_advert_welcome_panel', true );

    if($option != 1 && $option != 0){
        $option = 1;
    }

    // 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner
    //$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
    $hide = 0 == $option;
    if ( $hide )
	    $classes .= ' hidden';

    ?>
    <div id="advert-welcome-panel" class="<?php echo $classes; ?>">
        <?php wp_nonce_field( 'advert-welcome-panel-nonce', 'advertwelcomepanelnonce', false ); ?>		
        <a class="advert-welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>">Dismiss</a>
	    <div class="welcome-panel-content">
	    <h3><?php _e( 'Welcome to AdVert', 'ADVERT_TEXTDOMAIN' ); ?></h3>
	    <p class="about-description"><?php _e( 'We have assembled some links and information to get you started', 'ADVERT_TEXTDOMAIN' ); ?>:</p>
	    <div class="welcome-panel-column-container">
	    <div class="welcome-panel-column">
	    <h4><?php _e( 'Getting Started', 'ADVERT_TEXTDOMAIN' ); ?></h4>
	    <a class="button button-primary button-hero load-customize" href="<?php echo esc_url(admin_url( 'admin.php?page=advert-cp-general' )); ?>"><?php _e( 'General Settings', 'ADVERT_TEXTDOMAIN' ); ?></a>
	    <p>
        <a href="<?php echo esc_url(admin_url( 'admin.php?page=advert-cp-users' )); ?>"><?php _e( 'User Settings', 'ADVERT_TEXTDOMAIN' ); ?></a>
        &nbsp;or&nbsp;
        <a href="<?php echo esc_url(admin_url( 'admin.php?page=advert-cp-ads' )); ?>"><?php _e( 'Ad Settings', 'ADVERT_TEXTDOMAIN' ); ?></a>
        </p>
        <p>
        <?php
        $timezone = get_option('timezone_string') ? get_option('timezone_string') : __( 'Not set', 'ADVERT_TEXTDOMAIN' );   
        printf( __( 'Your current timezone is: <strong>%s</strong>. AdVert will use the timezone to track, update and manage your advertisements. If needed, you can update your timezone <a href="%s">here</a>', 'ADVERT_TEXTDOMAIN' ), $timezone, esc_url(admin_url( 'options-general.php#timezone_string' )) ); 
        ?>
        </p>
        </div>
	    <div class="welcome-panel-column">
        <h4><?php _e( 'Next Steps', 'ADVERT_TEXTDOMAIN' ); ?></h4>
        <ul>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-location' )); ?>" class="welcome-icon welcome-write-blog">Add a new Location</a></li>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-advertiser' )); ?>" class="welcome-icon welcome-write-blog">Add a new Advertiser</a></li>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-banner' )); ?>" class="welcome-icon welcome-write-blog">Add a new Banner</a></li>
	    <li><a href="<?php echo esc_url(admin_url( 'edit.php?post_type=advert-campaign' )); ?>" class="welcome-icon welcome-write-blog">Add a new Campaign</a></li>
	    </ul>
	    </div>
	    <div class="welcome-panel-column welcome-panel-last">
	    <h4><?php _e( 'More Actions', 'ADVERT_TEXTDOMAIN' ); ?></h4>
	    <ul>
        <?php if ( current_theme_supports( 'widgets' ) ) { ?>
	    <li><div class="welcome-icon welcome-widgets-menus"><?php _e( 'Add an AdVert', 'ADVERT_TEXTDOMAIN' ); ?>&nbsp;<a href="<?php echo esc_url(admin_url( 'widgets.php' )); ?>"><?php _e( 'widget', 'ADVERT_TEXTDOMAIN' ); ?></a></div></li>
	    <?php } ?>
        <li><a href="<?php echo esc_url(admin_url( 'admin.php?page=advert-analysis-overview' )); ?>" class="welcome-icon welcome-view-site"><?php _e( 'View Analysis', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
        <li><a href="<?php echo esc_url(admin_url( 'edit-tags.php?taxonomy=advert_category&post_type=advert-banner' )); ?>" class="welcome-icon welcome-add-page"><?php _e( 'Banner Categories', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    <li><a href="https://norths.co/advert/documentation/" class="welcome-icon welcome-learn-more"><?php _e( 'Learn more about AdVert', 'ADVERT_TEXTDOMAIN' ); ?></a></li>
	    </ul>
	    </div>
	    </div>
	    </div>
	    </div>
    <?php





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