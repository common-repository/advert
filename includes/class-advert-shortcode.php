<?php
/**
 * The actual AdVert Shortcode Output
 *
 * Goes through a number of checks to determine the correct location, then the campaign, and lastly, the actual banner to display.
 * Once the campaign is determined, there are more checks to determine if the advertiser has enough funds, if the campaign has enough funds.
 * Also, this function will check for campaign specific criteria for example: impression limits per person or total number of impressions
 *
 * @since 1.0.0
 */


/** Catch the AdVert Shortcode  */
add_shortcode( 'advert_location', 'advert_location_shortcode' );

function advert_location_shortcode($atts) {

//checks if the shortcode call is in the loop (post content)
if ( in_the_loop() && !is_single() ){
    return;
}

global $get_network_type;
global $main_site_id;
global $current_site_id;

if( $get_network_type === true && get_current_blog_id() != $main_site_id ){
    switch_to_blog($main_site_id);
}

global $nowrapper;
global $advert_counter;
global $advert_options;
$nowrapper = 1;

//if ads for post, page or post type are disabled
$post_page_switch = '';
if( array_key_exists('advert_allow_editors_turn_off_ads',$advert_options) ){
    $check_post_page = get_post_meta( get_the_ID(), 'advert_post_page_visible', true );
    $post_page_switch = intval($advert_options['advert_allow_editors_turn_off_ads']);

    if( $check_post_page == 1 && $post_page_switch == 1 ){
        return;
    }

    if( array_key_exists('advert_turn_off_ads_post_type-'.get_post_type(get_the_ID()),$advert_options)){
        return;
    }

}

$a = shortcode_atts( array('location_id' => 0), $atts );
$checker = get_post($a['location_id']);
if(!$checker || $checker->post_status != 'publish'){
   if( current_user_can('publish_adverts') ){
       return '<h1>AdVert Location Not Published</h1>';
   }
   else{
       return;
   }
}

$banners4locations = new WP_Query(
array('post_type'=>'advert-banner','posts_per_page'=> -1, 'meta_query' => array(
    array(
    'key'     => 'banner_location',
    'value'   => $a['location_id'],
    'compare' => '=',
    ),
    )
));
wp_reset_postdata();

if( $banners4locations->found_posts === 0 ){

    $advert_display_advertise_here = 0;
    if( array_key_exists('advert_display_advertise_here', $advert_options) ){  
        $advert_display_advertise_here = intval($advert_options['advert_display_advertise_here']);
    }

    $advert_display_advertise_here_text = '';
    if( array_key_exists('advert_display_advertise_here_text', $advert_options) ){  
        $advert_display_advertise_here_text = htmlspecialchars_decode($advert_options['advert_display_advertise_here_text']);
    }

    if($advert_display_advertise_here === 1){
        return $advert_display_advertise_here_text;    
    }
}

if( array_key_exists('count_override', $atts) ){  
    $advert_counter = $atts['count_override'];
    $nowrapper = 0;
}

$location = new ADVERT_Shortcode($a['location_id']);

if( $get_network_type === true && get_current_blog_id() != $main_site_id ){
    restore_current_blog();
}

return $location->html;
}


class AdVert_Shortcode{

    /* Public variables */
    public $html;
    public $banners;
    public $dummy_array1;
    public $dummy_array2;
    public $dummy_array3;
    public $rand_camp_id;
    public $rand_ad_id;
    public $campaigns;
    public $location_meta;
    public $advert_counter;
    public $nowrapper;
    public static $camp_meta = array('campaign_location','campaign_priority','campaign_start_date','campaign_stop_date','campaign_price_model','campaign_charges','campaign_budget','campaign_budget_value','campaign_impressions','campaign_ppimpressions', 'campaign_owner', 'campaign_viewable_status');

    /* Protected variables */
    protected $location_id;
    protected $banner_owner;
    protected $location;
    protected static $location_meta_key = array('location_rotation','location_price');
    protected $location_order = 1;


    /* Constructor */
    function __construct($location_id){

    $this->location_id = $location_id;
    $this->get_location();
    $this->get_campaigns();

    //if campaigns are empty, exit
    if(empty($this->campaigns)){return;}
        $this->get_banners();
        $this->get_html();
    }


    /** Determines the location  */
    function get_location(){
    $this->location = get_post($this->location_id);
    foreach(self::$location_meta_key as $key)
    $this->location_meta[$key] = get_post_meta($this->location->ID , $key , true);
    }



    /** Retrieves the campaigns associated with the location  */
    function get_campaigns(){
    $query = new WP_Query(
    array('post_type'=>'advert-campaign','posts_per_page'=> -1,'meta_key' => 'campaign_priority','orderby' => 'meta_value_num','order' => 'DESC' , 'meta_query' => array(
     array(
       'key'     => 'campaign_location',
       'value'   => $this->location_id,
       'compare' => '=',
      ),
     )
    ));
    wp_reset_postdata();
    $this->campaigns = $query->posts;
    }

    /** Retrieves the banners associated with the location and campaign  */
    function get_banners(){
    $this->banners = array();
    $this->dummy_array1 = array();//main
    $this->dummy_array2 = array();//campaign
    $this->dummy_array3 = array();//banner
    $this->dummy_campaign = array();//campaign
    $this->dummy_banner = array();//banner

    $loc_rotate = get_post_meta($this->location_id, 'location_rotation', true);

    //check the type of ad - image video text
    $loc_ad_type = array();
    if(get_post_meta($this->location_id, 'location_imagead', true) === '1'){
       array_push($loc_ad_type, 'image');
    }
    if(get_post_meta($this->location_id, 'location_videoad', true) === '1'){
       array_push($loc_ad_type, 'video');
    }
    if(get_post_meta($this->location_id, 'location_textad', true) === '1'){
       array_push($loc_ad_type, 'text');
    }

    //check options
    global $advert_options;

    $advert_negative_feedback = 0;
    if( array_key_exists('advert_feedback_hide_negative',$advert_options) ){
        $advert_negative_feedback = intval($advert_options['advert_feedback_hide_negative']);
    }

    //get banners
    $banners = get_posts(array('post_type'=>'advert-banner','posts_per_page'=> -1,'meta_key' => 'banner_owner','orderby' => 'meta_value_num' , 'order' => 'DESC'));

    //loop through each associated campaign in order to determine the banner to display using either priority or random
    foreach((array)$this->campaigns as $camp){

        if(self::is_available($camp->ID, $this->location_id)){

            //this reviews the campaign priority and location flow (random or priority) adds to the pool and the randomizers selects the campaign and banners based on probabilities in the array
            $i = 0;
            if($loc_rotate === 'random'){
                $campaign_priority = 1;
            }
            else{
                $campaign_priority = intval(get_post_meta($camp->ID, 'campaign_priority', true));
            }

            while( $i <= $campaign_priority ){             

                foreach( $banners as $banner ){
        
                $banner_image = '';
                $banner_video = '';
                $banner_text = '';

                    if($advert_negative_feedback === 1){
                        $con = '12 HOUR';
                        $clicked = ADVERT_Log::get_click_by('f', $banner->ID, $con, '3');
                    }
                    else{
                        $clicked = '0';
                    }

                    if($clicked === '0'){
                    $aid = get_post_meta($banner->ID, 'banner_owner', true);

                    if(has_post_thumbnail($banner->ID)){
                    $banner_image = 'image';
                    }
                    if(get_post_meta($banner->ID , 'banner_video_url' ,true) !=''){
                    $banner_video = 'video';
                    }
                    if(get_post_meta($banner->ID , 'banner_text_ad1' ,true) !='' || get_post_meta($banner->ID , 'banner_text_ad2' ,true) !=''){
                    $banner_text = 'text';
                    }

                        if ( $banner->banner_location == $this->location_id && intval($aid) == intval($camp->campaign_owner)){
                            if( in_array($banner_image, $loc_ad_type) || in_array($banner_video, $loc_ad_type) || in_array($banner_text, $loc_ad_type) ){
                                if($loc_rotate === 'random'){
                                    array_push($this->dummy_array2, array($banner->ID, $camp->ID));
                                }
                                else{
                                for($k=0; $k < $banner->banner_priority; $k++){
                                    array_push($this->dummy_array2, array($banner->ID, $camp->ID));
                                }
                                }
                            }
                        }
                    }
                }

            $i++;
            }
        }
    }

    if( count($this->dummy_array2) > 0 ){
        shuffle($this->dummy_array2);
        $rand_ids = array_rand($this->dummy_array2);
        $rand_ids = $this->dummy_array2[$rand_ids];
        $this->dummy_array3 = $rand_ids;
    }
    else{
        return;
    }

    }




    function get_html(){     

    global $advert_counter;
    global $nowrapper;
    global $advert_options;

    //simple counter to add if someone uses the same campaign/banner multiple times
    $advert_counter = $advert_counter + 1;

    //check the options
    $advert_display_advert_link = 0;
    $advert_display_adv = 0;
    $advert_feedback = 0;

    if( array_key_exists('advert_about_link',$advert_options) ){
        $advert_display_advert_link = intval($advert_options['advert_about_link']);
    }
    if( array_key_exists('advert_identify_ad',$advert_options) ){
        $advert_display_adv = intval($advert_options['advert_identify_ad']);
    }
    if( array_key_exists('advert_feedback',$advert_options) ){
        $advert_feedback = intval($advert_options['advert_feedback']);
    }

    //extra varables to identify banner id and campaign id from the dummy arrays
    if( count($this->dummy_array3) > 0 ){
    $aid = strval($this->dummy_array3[0]);
    $cid = strval($this->dummy_array3[1]);
    }

    //exit the function if empty ids
    if ( empty($aid) || empty($cid) )
    return;

    //check if image, video or text isset
    if( !has_post_thumbnail($aid) && get_post_meta($aid, 'banner_video_url', true) === '' && get_post_meta($aid, 'banner_text_ad1', true) === '' && get_post_meta($aid, 'banner_text_ad2', true) === '' )
    return;

    $curURL = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $custom = get_post_meta($aid, 'banner_custom_html', true);
    $location_enforce = intval(get_post_meta($this->location->ID, 'location_enforce', true));
    $theheight = get_post_meta($this->location->ID, 'location_height', true);
    $thewidth = get_post_meta($this->location->ID, 'location_width', true);

    //setup the wrapper for image, video or text and include the link (href)
    $link = $this->get_banner_link($aid, $cid, $curURL);

    $ww = '';
    $wh = '';
    $whww = '';

    if(!empty($thewidth) || !empty($theheight)){
    if($location_enforce === 1){
    if($thewidth){$ww='width:'.$thewidth.'px;';}
    if($theheight){$wh='height:'.$theheight.'px;';}
    $whww = 'style="'.$ww.$wh.'"';
    }
    else{
    if($thewidth){$ww='max-width:'.$thewidth.'px;';}
    if($theheight){$wh='max-height:'.$theheight.'px;';}
    $whww = 'style="'.$ww.$wh.'"';        
    }
    }

    //adding a timestamp to the banner being servered
    date_default_timezone_set("UTC");

    if($nowrapper === 1){
    $wrapper = '<div id="advert-zone-wraper'.$advert_counter.'" class="advert-zone-wrapper advert-trans40" data-robots="noindex"><div id="advert-zone'.$advert_counter.'" class="advert-zone '.get_post( $this->location->ID )->post_name.' advert-trans40" data-tstamp="'. time() .'" '.$whww.'>';
    }
    else{
    $wrapper = '<div id="advert-zone'.$advert_counter.'" class="advert-zone '.get_post( $this->location->ID )->post_name.' advert-trans40" data-tstamp="'. time() .'" '.$whww.'>';
    }

    if(empty($custom)){

    //display feedback, AdVert link encrypted abd the word "Advertisement", based on options selected in control panel
    $feedback_check = 0;
    if($advert_feedback === 1 && $advert_display_adv === 1 && $advert_display_advert_link === 1){
        $wrapper .= '<div class="advert-display-notice"><p class="advert-display-advertisement-text">'. __( 'Advertisement', 'ADVERT_TEXTDOMAIN' ) .'</p><div class="advert-choice advert-has-advertisement-text advert-trans70" title="'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'"><span id="advert-small-notice-text">'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'</span><span id="advert-small-notice-logo">a</span></div><div id="adf-'.$advert_counter.'" class="advert-display-feedback slide-feedback" style="display:none"><div class="advert-feedback-close slide-feedback" title="'. __( 'Close Feedback', 'ADVERT_TEXTDOMAIN' ) .'">X</div><div class="advert-feedback-table"><div class="advert-feedback-table-cell"><div class="advert-aftc-15734'.$aid.'" ><ul data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><li>'. __( 'This Ad is:', 'ADVERT_TEXTDOMAIN' ) .'</li><li><a href="#" rel="nofollow" data-feedback="1">'. __( 'Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" data-feedback="2">'. __( 'Not Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" rel="nofollow" data-feedback="3">'. __( 'Displayed too much', 'ADVERT_TEXTDOMAIN' ) .'</a></li></ul><p class="advert-feeback-return1">'. __( 'Thank you for your feedback', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feeback-return2">'. __( 'Your feedback has been updated', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feedback-bl"><a href="https://norths.co/advert/#aboutus" target="_blank">'. __( 'About AdVert', 'ADVERT_TEXTDOMAIN' ) .'</a></p></div></div></div></div>';
        $feedback_check = 1;
    }
    elseif($advert_feedback === 1 && $advert_display_adv === 1){
        $wrapper .= '<div class="advert-display-notice"><p class="advert-display-advertisement-text">'. __( 'Advertisement', 'ADVERT_TEXTDOMAIN' ) .'</p><div class="advert-choice advert-has-advertisement-text advert-trans70" title="'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'"><span id="advert-small-notice-text">'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'</span><span id="advert-small-notice-logo">a</span></div><div id="adf-'.$advert_counter.'" class="advert-display-feedback slide-feedback" style="display:none"><div class="advert-feedback-close slide-feedback" title="'. __( 'Close Feedback', 'ADVERT_TEXTDOMAIN' ) .'">X</div><div class="advert-feedback-table"><div class="advert-feedback-table-cell"><div class="advert-aftc-15734'.$aid.'" ><ul data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><li>'. __( 'This Ad is:', 'ADVERT_TEXTDOMAIN' ) .'</li><li><a href="#" rel="nofollow" data-feedback="1">'. __( 'Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" data-feedback="2">'. __( 'Not Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" rel="nofollow" data-feedback="3">'. __( 'Displayed too much', 'ADVERT_TEXTDOMAIN' ) .'</a></li></ul><p class="advert-feeback-return1">'. __( 'Thank you for your feedback', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feeback-return2">'. __( 'Your feedback has been updated', 'ADVERT_TEXTDOMAIN' ) .'</p></div></div></div></div>';
        $feedback_check = 1;
    }
    elseif($advert_feedback === 1 && $advert_display_advert_link === 1){
        $wrapper .= '<div class="advert-display-notice"><div class="advert-choice advert-trans70" title="'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'"><span id="advert-small-notice-text">'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'</span><span id="advert-small-notice-logo">a</span></div><div id="adf-'.$advert_counter.'" class="advert-display-feedback slide-feedback" style="display:none"><div class="advert-feedback-close slide-feedback" title="'. __( 'Close Feedback', 'ADVERT_TEXTDOMAIN' ) .'">X</div><div class="advert-feedback-table"><div class="advert-feedback-table-cell"><div class="advert-aftc-15734'.$aid.'" ><ul data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><li>'. __( 'This Ad is:', 'ADVERT_TEXTDOMAIN' ) .'</li><li><a href="#" rel="nofollow" data-feedback="1">'. __( 'Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" data-feedback="2">'. __( 'Not Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" rel="nofollow" data-feedback="3">'. __( 'Displayed too much', 'ADVERT_TEXTDOMAIN' ) .'</a></li></ul><p class="advert-feeback-return1">'. __( 'Thank you for your feedback', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feeback-return2">'. __( 'Your feedback has been updated', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feedback-bl"><a href="https://norths.co/advert/#aboutus" target="_blank">'. __( 'About AdVert', 'ADVERT_TEXTDOMAIN' ) .'</a></p></div></div></div></div>';
        $feedback_check = 1;
    }
    elseif($advert_feedback === 1){
        $wrapper .= '<div class="advert-display-notice"><div class="advert-choice advert-trans70" title="'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'"><span id="advert-small-notice-text">'. __( 'AdVert Feedback', 'ADVERT_TEXTDOMAIN' ) .'</span><span id="advert-small-notice-logo">a</span></div><div id="adf-'.$advert_counter.'" class="advert-display-feedback slide-feedback" style="display:none"><div class="advert-feedback-close slide-feedback" title="'. __( 'Close Feedback', 'ADVERT_TEXTDOMAIN' ) .'">X</div><div class="advert-feedback-table"><div class="advert-feedback-table-cell"><div class="advert-aftc-15734'.$aid.'" ><ul data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><li>'. __( 'This Ad is:', 'ADVERT_TEXTDOMAIN' ) .'</li><li><a href="#" rel="nofollow" data-feedback="1">'. __( 'Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" data-feedback="2">'. __( 'Not Relevant', 'ADVERT_TEXTDOMAIN' ) .'</a></li><li><a href="#" rel="nofollow" data-feedback="3">'. __( 'Displayed too much', 'ADVERT_TEXTDOMAIN' ) .'</a></li></ul><p class="advert-feeback-return1">'. __( 'Thank you for your feedback', 'ADVERT_TEXTDOMAIN' ) .'</p><p class="advert-feeback-return2">'. __( 'Your feedback has been updated', 'ADVERT_TEXTDOMAIN' ) .'</p></div></div></div></div>';
        $feedback_check = 1;
    }
    elseif($advert_display_adv === 1){
        $wrapper .= '<div class="advert-display-notice"><p class="advert-display-advertisement-text">'. __( 'Advertisement', 'ADVERT_TEXTDOMAIN' ) .'</p></div>';
    }

    $html = '';

    //external link redirect
    $external_link = get_post_meta($aid,'banner_link',true);
    $site_link = get_site_url();

    //image banner
    if( has_post_thumbnail($aid) && get_post_meta($aid, 'banner_video_url', true) == '' ){
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($aid), 'full' );
        if( !empty($external_link) ){
        $html .= '<a id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-image" href="'.$site_link.'?AdVert=1&data='.encryptLink($link['data']).'&nonce='.$link['nonce'].'&redir='.$link['redir'].'" rel="nofollow" target="_'.get_post_meta($aid,'banner_target',true).'" data-ads="'.encryptLink($link['data']).'" data-nonce="'.wp_create_nonce( $link['data'] ).'">';
        $html .= '<img src="'.$thumb['0'].'">';
        }
        else{
        $html .= '<div id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-image" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><img src="'.$thumb['0'].'">';    
        }
    }
    //video banner
    elseif(get_post_meta($aid, 'banner_video_url', true) != ''){
        $poster = '';
        $thumb = '';
        $onended = '';
        if( array_key_exists('advert_hide_videos',$advert_options) ){
            $onended = intval($advert_options['advert_hide_videos']);
        }
        if( $onended === 1){
            $onended = 'onended="advertVideoEnded(this.id)"';        
        }
        if($poster = wp_get_attachment_image_src( get_post_thumbnail_id($aid),  'full' )){$thumb = '<div class="advert-nvsupport"><img src="'.$poster[0].'"></div>';$poster = 'poster="'.$poster[0].'"';}
        if( !empty($external_link) ){
        $html .= '<a id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-video" href="'.$site_link.'?AdVert=1&data='.encryptLink($link['data']).'&nonce='.$link['nonce'].'&redir='.$link['redir'].'" rel="nofollow" target="_'.get_post_meta($aid,'banner_target',true).'" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><video id="advert-zone'.$advert_counter.'-video" class="advert-video" ' . $poster . $whww . $onended . '><source src="'.get_post_meta($aid, 'banner_video_url', true).'" type="video/mp4" /></video>';
        $html .= $thumb;
        }
        else{
        $html .= '<div id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-video" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'"><video id="advert-zone'.$advert_counter.'-video" class="advert-video" ' . $poster . $whww . $onended . '><source src="'.get_post_meta($aid, 'banner_video_url', true).'" type="video/mp4" /></video>';
        $html .= $thumb;        
        }
    }
    //text banner
    elseif(get_post_meta($aid, 'banner_text_ad1', true) !='' || get_post_meta($aid, 'banner_text_ad2', true) !=''){
        $txtbrk = '';
        if(get_post_meta($aid, 'banner_text_ad1', true) !='' && get_post_meta($aid, 'banner_text_ad2', true) !=''){$txtbrk = '</p><p class="advert-text-ad2">';}
        if( !empty($external_link) ){
        $html .= '<a id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-text" href="'.$site_link.'?AdVert=1&data='.encryptLink($link['data']).'&nonce='.$link['nonce'].'&redir='.$link['redir'].'" rel="nofollow" target="_'.get_post_meta($aid,'banner_target',true).'" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'">' . htmlspecialchars_decode(get_post_meta($this->location->ID, 'location_textad_html1', true)) . '<p class="advert-text-ad1">' . get_post_meta($aid, 'banner_text_ad1', true) . $txtbrk . get_post_meta($aid, 'banner_text_ad2', true) . '</p>' . htmlspecialchars_decode(get_post_meta($this->location->ID, 'location_textad_html2', true));    
        }
        else{
        $html .= '<div id="advert-wrap-'.$advert_counter.'" class="advert-wrapper advert-is-text" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'">' . htmlspecialchars_decode(get_post_meta($this->location->ID, 'location_textad_html1', true)) .'<p class="advert-text-ad1">'.get_post_meta($aid, 'banner_text_ad1', true) . $txtbrk . get_post_meta($aid, 'banner_text_ad2', true) . '</p>' . htmlspecialchars_decode(get_post_meta($this->location->ID, 'location_textad_html2', true));            
        }
    }




    }
    //display custom HTML here
    else{
        $html .= '<div id="advert-wrap-'.$advert_counter.'" class="advert-wrapper" data-ads="'.encryptLink($link['data']).'"  data-nonce="'.wp_create_nonce( $link['data'] ).'">';
        $html .= htmlspecialchars_decode($custom);
    }

    if(empty($custom) && !empty($external_link)){
        $html .= '</a>';
    }
    else{
        $html .= '</div>';
    }

    if($feedback_check === 1){
    $wrapper_end = '</div></div>';
    }
    else{
    $wrapper_end = '</div>';    
    }
    if($nowrapper === 1){
    $wrapper_end .= '</div>';
    }

    $this->html = $wrapper . $html . $wrapper_end;
    }



    /**
     * The actual link output
     *
     * @since 1.0.0
     *
     * @see use in function get_html()
     */
    function get_banner_link($banner, $campid, $curURL){
    $query_arg = [];
    $query_args['data'] = $banner . '-|-' . $campid . '-|-' . get_post_meta($banner , 'banner_owner' ,true) . '-|-' . $this->location->ID . '-|-' . $curURL;
    $query_args['nonce'] = wp_create_nonce($query_args['data']);
    $query_args['redir'] = urlencode(get_post_meta($banner,'banner_link',true));
    return $query_args;
    }



    /**
     * Checks if the campaign is available
     *
     * Checks: start date, stop date, campaign funds, advertiser funds and other options set by user
     *
     * @since 1.0.0
     *
     * @see use in function get_banners()
     */
    static function is_available($campaign_id, $loc){

        if($campaign_id == 0)
        return true;

        if(!($campaign = get_post($campaign_id)))
        return false;

        if($campaign->post_status != 'publish')
            return false;

        //get the current wordpress time set by the user under settings
        $now = current_time('mysql');

        //store the data in an array
        foreach(self::$camp_meta as $data){
            $meta[$data] = get_post_meta($campaign_id, $data ,true);
        }

        //check if the campaign is active or paused
        if( $meta['campaign_viewable_status'] != 'active' )
        return false;

        //get specific options
        $company_credits  = get_post_meta($meta['campaign_owner'], 'company_credits' , true);
        $campaign_charges = get_post_meta($campaign_id, 'campaign_charges' , true);
        $location_price   = get_post_meta($loc, 'location_price' , true);
        $bypass           = intval(get_post_meta($meta['campaign_owner'], 'advert_bypass_adcredits' , true));

        if( $bypass != 1 ){

            if( empty($company_credits) )
                return false;

        }
      
        //check the start date if set 
        if(!empty($meta['campaign_start_date']) && strtotime($meta['campaign_start_date']) > strtotime(current_time('m/d/Y')))
            return false;

        //check the stop date if set 
        if(!empty($meta['campaign_stop_date']) && strtotime($meta['campaign_stop_date']) < strtotime(current_time('m/d/Y'))){
            
            //change post status to archive
            do_action('advert_auto_archive_post', $campaign_id);

            return false;
        }

        $log = ADVERT_Log::get_log_by('camp_id',$campaign_id);

        $valid = 0;

        //checks cookies if set for campaign limits for impressions
        if(isset($_COOKIE['avviewed'])){
            try{
                $person_cookie = stripslashes(decryptLink(htmlspecialchars($_COOKIE['avviewed']), 'campimp'));
                if( is_serialized($person_cookie) ){
                    $person = unserialize($person_cookie);
                    $valid  = intval(advert_is_utf8($person_cookie));
                }
            }
            catch(Exception $e){
                unset($_COOKIE['avviewed']);
                $person = 0;
            }
            if($valid != 1){
            unset($_COOKIE['avviewed']);
            $person = 0;
            }
        }
        else{
            $person = 0;    
        }

        if($log->num >= $meta['campaign_impressions'] && $meta['campaign_impressions'] > 0)
        return false;

        if(isset($person['camp_id']) && isset($person[$campaign_id]) && $person[$campaign_id] >= $meta['campaign_ppimpressions'] && $meta['campaign_ppimpressions'] > 0)
        return false;

        //advertiser bypass adcredits
        if( $bypass === 1 )
            return true;

        if($meta['campaign_budget'] === 'fixed'){

            if($meta['campaign_budget_value'] > 0 && $log->payment >= $meta['campaign_budget_value'] || $company_credits < $location_price)
            return false;

        }
        elseif($meta['campaign_budget'] === 'per_day'){

            $con = date('Y-m-d 00:00:00',strtotime($now));
            $log_perday = ADVERT_Log::get_log_by('camp_id',$campaign_id,'i',$con);

            if($meta['campaign_budget_value'] > 0  && $log_perday->payment >= $meta['campaign_budget_value'] || $company_credits < $location_price)
            return false;

        }
        else{
            return;
        }

        //if pricing model is CPP remove the funds when the campaign starts
        if($meta['campaign_price_model']=='cpp' && intval($meta['campaign_charges']) == 0){
        
            update_post_meta($campaign_id, 'campaign_charges', $meta['campaign_budget_value'] );

            $transaction = apply_filters('remove_adcredits', $meta['campaign_owner'], $meta['campaign_budget_value'], '', 'cpp');   

            if($transaction)
            do_action('calc_adcredits', $meta['campaign_owner']);

        }

        //double check for available adcredits
        if ( $company_credits > 0 ){
            return true;
        }
        else{
            return false;
        }

    }

}//end AdVert Shortcode Class