<?php


//inset ad locations into wordpress without having to place the shortcodes manually
add_filter('the_content', 'advert_place_content_ads', 20);
add_action('wp_enqueue_scripts', 'advert_frontend_scripts');
add_action('wp_head', 'advert_insert_head');
add_action('wp_footer', 'advert_insert_bottom');

//modify toolbar
add_action('admin_bar_menu', 'advert_frontend_modify_toolbar', 999);

//auto archive
add_action('advert_auto_archive_post', 'advert_auto_archive', 10, 1);

function advert_auto_archive($post_id){
    
    $update_advert = array(
        'ID'          => $post_id,
        'post_status' => 'advert-archive',
    );
    wp_update_post( $update_advert );

}


/**
    * Encrypt Link
    *
    * @since 1.0.0
    *
    */
function encryptLink($link,$type='') {

    global $advert_options;

    //adds an extra "key" at the end if deleted or left blank - leaving the option empty is not recommended
    $advert_encryption = '';
    if( array_key_exists('advert_decode_key',$advert_options) ){
        $advert_encryption = $advert_options['advert_decode_key'];
    }
    //add an extra encryption value incase the key is not set or removed
    //the encryption key
    $key = $advert_encryption.'238378';

    //encryption
    $link = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $link, MCRYPT_MODE_ECB);
    $link = base64_encode($link);
    $link = urlencode($link);
    //end encryption

    return $link;

}

/**
    * Decrypt Link
    *
    * @since 1.0.0
    *
    */
function decryptLink($link,$type=''){

    global $advert_options;

    $advert_encryption = '';
    if( array_key_exists('advert_decode_key',$advert_options) ){
        $advert_encryption = $advert_options['advert_decode_key'];
    }
    //encryption key
    //add an extra encryption value incase the key is not set or removed
    $key = $advert_encryption.'238378';

    // decryption happen here
    $link = urldecode($link);
    $link = base64_decode($link);
    $link = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $link, MCRYPT_MODE_ECB));

    return $link;

}


function advert_insert_head() {

    /** Include the Ajax library on the front end */
    $html = '<script type="text/javascript">';
    $html .= 'var avajaxurl = "' . esc_url(admin_url( 'admin-ajax.php' )) . '"';
    $html .= '</script>';

    /** Include the AdVert blocking scripts on the front end */
    $html .= '<style type="text/css">.advert-display-advertisement-text{text-align:center;}#avblocked-notice{display:none;position:fixed;z-index:9999999999;top:0;left:0;right:0;bottom:0;width:100%;height:100%;}#avblocked-notice-overlay{width:100%;height:100%;text-align:center;}#avblocked-notice-center-wrap{height:100%;max-width:500px;margin:0 auto;-webkit-transform-style:preserve-3d;-moz-transform-style:preserve-3d;transform-style:preserve-3d;}#avblocked-notice-wrap{position:relative;top:50%;-webkit-transform:translateY(-50%);-moz-transform:translateY(-50%);-ms-transform:translateY(-50%);-o-transform:translateY(-50%);transform:translateY(-50%);max-width:500px;max-height:100%;overflow:auto;background-color:#fff;margin:0 auto;padding:40px 40px 20px 40px;border:1px solid #e4e4e4;color:#333;}.advert-invisible{visibility:hidden;}.avblocked-close{position:absolute;top:0px;right:0px;margin: 10px 15px 0 0 !important;font-family: "Arial Black", Gadget, sans-serif;font-size: 22px;color: #e4e4e4;cursor: pointer;}#avblocked-notice-wrap p{margin:0 0 20px 0;}.avbg60{ position: fixed;z-index:999999999;left: 0;right: 0;top: 0;bottom: 0;background-color: rgb(255, 255, 255); background-color: rgba(255, 255, 255, 0.7); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#26ffffff, endColorstr=#26ffffff); -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#26ffffff, endColorstr=#26ffffff)";}.avblocked-close:hover{color:#333;}</style>';

    echo $html;

}


function advert_insert_bottom() {

    global $advert_options;

    $advert_display_notice = 0;
    if( array_key_exists('advert_display_notice_blocked',$advert_options) ){
        $advert_display_notice = intval($advert_options['advert_display_notice_blocked']);
    }

    $advert_display_notice_text = '';
    if( array_key_exists('advert_display_notice_blocked_text',$advert_options) ){
        $advert_display_notice_text = $advert_options['advert_display_notice_blocked_text'];
    }
    else{
        $advert_display_notice_text = __( 'You have blocked our Ads, we understand...you should know that we use AdVert, a user friendly Ad System. AdVert provides you an option to control the type of Ads shown...you are now in control. If you see something too much, click that option, or if you see something you like you can give that feedback too.<br /><br /><a href="https://norths.co/advert/">About AdVert</a>', 'ADVERT_TEXTDOMAIN' );    
    }

    $avblocked_cookie = (isset($_COOKIE['avblocked']) ? stripslashes(sanitize_text_field($_COOKIE['avblocked'])) : 'No');

    if( $advert_display_notice === 1 && $avblocked_cookie === 'No'){

        echo '<div id="avblocked-notice" data-robots="noindex">';
        echo '<div id="avblocked-notice-overlay" class="avbg60">';
        echo '<div id="avblocked-notice-center-wrap">';
        echo '<div id="avblocked-notice-wrap">';
        echo '<div class="avblocked-close">X</div>';
        echo  '<p>'. $advert_display_notice_text . '<br /><br />'. __( 'If this notice continues to show daily, your cookies are either disabled or this website has manually changed the code.', 'ADVERT_TEXTDOMAIN' ) .'<br /><br /><a href="https://norths.co/advert/">' . __( 'About AdVert', 'ADVERT_TEXTDOMAIN' ) .'</a></p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

    }

    /** check if user string has similar bot names -- if it does advert will not count impressions or clicks  */
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
        echo '<div id="avbded581" style="display:none;"></div>';
    } 

    /** Include the AdVert blocking scripts on the front end */
    $html = '<script>function setCookie(cname, cvalue, exdays) { var d = new Date(); d.setTime(d.getTime() + (exdays*24*60*60*1000)); var expires = "expires="+d.toUTCString(); document.cookie = cname + "=" + cvalue + "; path=/;" + expires; }</script>';
    $html .= '<script>var isAdblockActive=true</script>';
    $html .= '<script type="text/javascript" src="'. ADVERT_PLUGIN_URL . 'js/ads.js"></script>';
    $html .= '<script>jQuery(document).ready(function($) { if (isAdblockActive) {console.log("You have blocked our Ads, we understand...you should know that we use AdVert, a user friendly Ad System. AdVert provides you an option to control the type of Ads shown...you are now in control. If you see something too much, click that option, or if you see something you like you can give that feedback too. Visit https://norths.co/advert/ to learn more about AdVert.");$(".advert-zone").addClass("advert-invisible");$("#avblocked-notice").fadeIn("slow");setCookie("avblocked", "Yes", 7);} else{setCookie("avblocked", "NA", 2);}$(".avblocked-close").click(function () { $("#avblocked-notice").fadeOut(); }); });</script>';

    echo $html;

}


function advert_place_content_ads( $content ) {

    global $get_network_type;
    global $main_site_id;
    global $current_site_id;

    if ( is_single() && is_main_query() && in_the_loop() ){

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            switch_to_blog($main_site_id);
        }

        $standard_locations = get_option('advert_standard_locations');

        if(is_array($standard_locations)){
        if(array_key_exists('beforecontent', $standard_locations)){
            if(get_post_status($standard_locations['beforecontent']) === 'publish'){
                $scode = '[advert_location location_id="'.$standard_locations['beforecontent'].'"]';
                $content =  '<br />' . do_shortcode($scode) . '<br />' . $content;
            }
        }
        if(array_key_exists('aftercontent', $standard_locations)){
            if(get_post_status($standard_locations['aftercontent']) === 'publish'){
                $scode = '[advert_location location_id="'.$standard_locations['aftercontent'].'"]';
                $content =  $content . '<br />' . do_shortcode($scode) . '<br />';
            }
        }
        }

        if( $get_network_type === true && $current_site_id != $main_site_id ){
            restore_current_blog();
        }

    }

    return $content;

}


function advert_is_utf8($str) {
    return (bool) preg_match('//u', $str);
}


function advert_frontend_modify_toolbar( $wp_admin_bar ) {

    if(is_admin())
    return;

    global $advert_options;

    if ( current_user_can( 'edit_adverts' ) ){
        if ( !current_user_can( 'publish_adverts' ) && current_user_can( 'edit_adverts' ) ){

            //add a parent item
	        $args = array(
		        'id'    => 'advert_dashboard_group',
                'title' => '<span class="ab-icon advert-toolbar-logo">a</span><span class="screen-reader-text">'. __( 'AdVert Dashboard', 'ADVERT_TEXTDOMAIN' ) .'</span>',
                'href'  => esc_url(admin_url( 'admin.php?page=advert-user' )),
                'meta'  => array( 'class' => 'advert-toolbar-group' )
	        );
	        $wp_admin_bar->add_node( $args );

            //add a child item to our parent item
	        $args = array(
		        'id'     => 'advert_dashboard_toolbar',
		        'title'  => __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ),
                'href'   => esc_url(admin_url( 'admin.php?page=advert-user' )),
		        'parent' => 'advert_dashboard_group'
	        );
	        $wp_admin_bar->add_node( $args );

            //check if user has rights to view analysis
            $user_analysis = 0;
            if(array_key_exists('advert_allow_analysis_users', $advert_options)){
                $user_analysis = intval($advert_options['advert_allow_analysis_users']);
            }

            if($user_analysis === 1){

            // add a child item to our parent item
	            $args = array(
		            'id'     => 'advert_analysis_toolbar',
		            'title'  => __( 'Analysis', 'ADVERT_TEXTDOMAIN' ),
                    'href'   => esc_url(admin_url( 'admin.php?page=advert-analysis-overview' )),
		            'parent' => 'advert_dashboard_group'
	            );
	            $wp_admin_bar->add_node( $args );

            }
    
        }

        if ( current_user_can( 'publish_adverts' ) ){

            //add a parent item
	        $args = array(
		        'id'    => 'advert_dashboard_group',
                'title' => '<span class="ab-icon advert-toolbar-logo">a</span><span class="screen-reader-text">'. __( 'AdVert Dashboard', 'ADVERT_TEXTDOMAIN' ) .'</span>',
                'href'  => esc_url(admin_url( 'admin.php?page=advert' )),
                'meta'  => array( 'class' => 'advert-toolbar-group' )
	        );
	        $wp_admin_bar->add_node( $args );

            //add a child item to our parent item
	        $args = array(
		        'id'     => 'advert_dashboard_toolbar',
		        'title'  => __( 'Dashboard', 'ADVERT_TEXTDOMAIN' ),
                'href'   => esc_url(admin_url( 'admin.php?page=advert' )),
		        'parent' => 'advert_dashboard_group'
	        );
	        $wp_admin_bar->add_node( $args );

            //add a child item to our parent item
	        $args = array(
		        'id'     => 'advert_analysis_toolbar',
		        'title'  => __( 'Analysis', 'ADVERT_TEXTDOMAIN' ),
                'href'   => esc_url(admin_url( 'admin.php?page=advert-analysis-overview' )),
		        'parent' => 'advert_dashboard_group'
	        );
	        $wp_admin_bar->add_node( $args );

            //add a child item to our parent item
	        $args = array(
		        'id'     => 'advert_control_panel_toolbar',
		        'title'  => __( 'Control Panel', 'ADVERT_TEXTDOMAIN' ),
                'href'   => esc_url(admin_url( 'admin.php?page=advert-cp-general' )),
		        'parent' => 'advert_dashboard_group'
	        );
	        $wp_admin_bar->add_node( $args );

        }
    }

}


/**
    * Loads front end css, js and localizes AJAX for AD Logging
    *
    * @since 1.0.0
    */
function advert_frontend_scripts() {

    $version = new AdVert_For_Wordpress();
    $version = $version->get_advert_version();

    wp_register_script( 'advert',  ADVERT_PLUGIN_URL . 'js/advertfe.min.js' ,array('jquery'), $version, true);
    $url = array( 'url' => esc_url(admin_url('admin-ajax.php')) );
    wp_localize_script( 'advert', 'ajax', $url );

    wp_enqueue_script( 'advert' );
    wp_enqueue_style('advert-style', ADVERT_PLUGIN_URL . 'css/advertfe.min.css');

    if ( wp_is_mobile() )
    wp_enqueue_script( 'jquery-touch-punch' );

}