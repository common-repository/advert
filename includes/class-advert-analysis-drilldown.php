<?php

class AdVert_Analysis_Drilldown {


    protected $queryitems;
    protected $queryitems2;
    protected $queryitems3;
    protected $queryitems4;
    protected $queryitems5;

    protected $status;
    protected $advertiser;
    protected $campaign;
    protected $banner;
    protected $start;
    protected $stop;
    protected $location; 


    public function __construct() {

        $this->advert_analysis_load_data();
        $this->advert_analysis_screen_options();
        $this->do_advert_analysis_drilldown_page();

    }


    public function advert_analysis_load_data(){

        $status     = '';
        $advertiser = '';
        $campaign   = '';
        $banner    = '';
        $start      = '';
        $stop       = '';
        $location   = '';

        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && $_POST['post_type'] == 'admin_analysis_options'  && $_POST['originalaction'] == 'adminanalysis' && current_user_can('edit_adverts') ){
            if ( !is_user_logged_in() || ! wp_verify_nonce( $_POST['admin-analysis'], 'admin_analysis' ) ){
                print 'Woah, whats really going on...?';
                return;
            }

            $status_array = array('any','active','archive');
            if(isset($_POST['advert-analysis-status']) && in_array($_POST['advert-analysis-status'],$status_array)){$status = sanitize_text_field($_POST['advert-analysis-status']);}

            if(!current_user_can('publish_adverts')){
                $user_id = get_current_user_id();
                $company_id = get_user_meta($user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
                $advertiser = $company_id;
            }
            else{
                if(isset($_POST['advert-analysis-advertiser']) && intval($_POST['advert-analysis-advertiser']) > 0){sanitize_text_field($advertiser = $_POST['advert-analysis-advertiser']);}   
            }

            if(isset($_POST['advert-analysis-campaign']) && intval($_POST['advert-analysis-campaign']) > 0){$campaign = sanitize_text_field($_POST['advert-analysis-campaign']);}
            if(isset($_POST['advert-analysis-banner']) && intval($_POST['advert-analysis-banner']) > 0){$banner = sanitize_text_field($_POST['advert-analysis-banner']);}
            if(isset($_POST['advert-analysis-start']) && strtotime($_POST['advert-analysis-start']) != false){$start = sanitize_text_field($_POST['advert-analysis-start']);}
            if(isset($_POST['advert-analysis-stop'])  && strtotime($_POST['advert-analysis-stop']) != false){$stop = sanitize_text_field($_POST['advert-analysis-stop']);}

            if( current_user_can('publish_adverts') ){
                if(isset($_POST['advert-analysis-location']) && intval($_POST['advert-analysis-location']) > 0){$location = sanitize_text_field($_POST['advert-analysis-location']);}    
            }

            $this->status     = $status;
            $this->advertiser = $advertiser;
            $this->campaign   = $campaign;
            $this->banner    = $banner;
            $this->start      = $start;
            $this->stop       = $stop;
            $this->location   = $location;

            $this->get_analysis_data($status,$advertiser,$campaign,$banner,$start,$stop,$location);

        }
        else{

            if( !current_user_can('edit_adverts') )
                return;

            //advertiser
            if(!current_user_can('publish_adverts')){
                $user_id = get_current_user_id();
                $company_id = get_user_meta($user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
                $advertiser = $company_id;
            }
            else{
                if( isset($_GET['advert-analysis-advertiser']) && intval($_GET['advert-analysis-advertiser']) > 0 ){
                    $advertiser = sanitize_text_field($_GET['advert-analysis-advertiser']);
                }    
            }

            //get data if available
            if ( isset( $_GET['post'] ) ){

                $nonce = ( isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '');
                if ( wp_verify_nonce( $nonce, 'advert-analysis-link' ) && current_user_can('edit_adverts') ){

                    //status
                    $status_array = array('any','active','archive');
                    if( isset($_GET['advert-analysis-status']) && in_array($_GET['advert-analysis-status'],$status_array) ){
                        $status = sanitize_text_field($_GET['advert-analysis-status']);    
                    }

                    //campaign
                    if( isset($_GET['advert-analysis-campaign'])  && intval($_GET['advert-analysis-campaign']) > 0 ){
                        $campaign = sanitize_text_field($_GET['advert-analysis-campaign']);    
                    }

                    //banner
                    if( isset($_GET['advert-analysis-banner']) && intval($_GET['advert-analysis-banner']) > 0 ){
                        $banner = sanitize_text_field($_GET['advert-analysis-banner']);    
                    }

                    //start
                    if( isset($_GET['advert-analysis-start'])  && strtotime(urldecode($_GET['advert-analysis-start'])) != false ){
                        $start = sanitize_text_field(urldecode($_GET['advert-analysis-start']));    
                    }

                    //stop
                    if( isset($_GET['advert-analysis-stop']) && strtotime(urldecode($_GET['advert-analysis-stop'])) != false ){
                        $stop = sanitize_text_field(urldecode($_GET['advert-analysis-stop']));    
                    }

                    //location
                    if( isset($_GET['advert-analysis-location']) && intval($_GET['advert-analysis-location']) > 0 && current_user_can('publish_adverts') ){
                        $location = sanitize_text_field($_GET['advert-analysis-location']);    
                    }

                }

            }

            $this->status     = $status;
            $this->advertiser = $advertiser;
            $this->campaign   = $campaign;
            $this->banner    = $banner;
            $this->start      = $start;
            $this->stop       = $stop;
            $this->location   = $location;

            $this->get_analysis_data($status,$advertiser,$campaign,$banner,$start,$stop,$location);

        }

    }



    public function get_analysis_data($status,$advertiser,$campaign,$banner,$start,$stop,$location){

        global $wpdb;
        $log_table = $wpdb->prefix . 'advert_logged';
        //$log_table_posts = $wpdb->prefix . 'posts';

        if($status === 'active'){$thestatus = "publish";}
        elseif($status === 'archive'){$thestatus = "archive";}

        if($advertiser != 'all_advertisers' && $advertiser != '' ){
        $conditions = ' WHERE adv_id = ' . $advertiser;
        if($campaign != 'all_campaigns' && $campaign != ''){$conditions .= ' AND camp_id = ' . $campaign;}
        if($banner != 'all_banners' && $banner != ''){$conditions .= ' AND banner_id = ' . $banner;}
        }
        elseif($campaign != 'all_campaigns' && $campaign != ''){
        $conditions = ' WHERE camp_id = ' . $campaign;
        if($banner != 'all_banners' && $banner != ''){$conditions .= ' AND banner_id = ' . $banner;}
        }
        elseif($banner != 'all_banners' && $banner != ''){
        $conditions = ' WHERE banner_id = ' . $banner;
        }
        elseif($location != 'all_locations' && $location !=''){
        if( empty($advertiser) && empty($campaign) && empty($banner) ){$conditions = ' WHERE location_id = ' . $location;}
        else{$conditions .= ' AND location_id = ' . $location;}
        }
        else{
        $conditions = '';
        }

        $time_conditions = '';

        if( isset($start) && $start != '' || isset($stop) && $stop != '' ){
        if(isset($start) && !empty($start)){
        if($conditions != ''){$time_conditions .= ' AND time >= "'.date('Y-m-d 00:00:00',strtotime($start)).'" ';}
        else{$time_conditions .= ' WHERE time >= "'.date('Y-m-d 00:00:00',strtotime($start)).'" ';}
        }

        if(isset($stop) && !empty($stop)){
        if($conditions != '' || $time_conditions !=''){$time_conditions .= ' AND time <= "'.date('Y-m-d 23:59:59',strtotime($stop)).'" ';}
        else{$time_conditions .= ' WHERE time <= "'.date('Y-m-d 23:59:59',strtotime($stop)).'" ';}
        }
        }
        else{
        if($conditions != ''){}
        else{$current_date = current_time('Y-m-d');$time_conditions .= " WHERE DATE(time) >= DATE_SUB('{$current_date}', INTERVAL 1 DAY) ";}
        }

        if ( !empty($_GET['orderby']) ){
        if( $_GET['order'] === 'asc'){$orderby = ' ORDER BY ' . $_GET["orderby"] . ' ASC ';}
        else{$orderby = ' ORDER BY ' . $_GET["orderby"] . ' DESC ';}
        }
        else{$orderby = '';}

        $queryitems = $wpdb->get_results(" SELECT * FROM $log_table $conditions $time_conditions $orderby ");

        if ($time_conditions !='' && $start === $stop && $start !='' && $stop !='' && isset($start) && isset($stop) ){$selectfrom = " CONCAT(HOUR(time), ':00') "; $groupby = " GROUP BY HOUR(timestamp) ORDER BY HOUR(timestamp) ";}
        else{$selectfrom = " DATE(time) "; $groupby = " GROUP BY timestamp ORDER BY timestamp ";}

        $queryitems2 = $wpdb->get_results("
        SELECT $selectfrom timestamp, COUNT(CASE WHEN typeof = 'i' THEN 1 END) imp, COUNT(CASE WHEN typeof = 'c' THEN 1 END) click
        FROM $log_table
        $conditions
        $time_conditions
        $groupby
        ");

        $queryitems3 = $wpdb->get_results(" SELECT SUM(CASE WHEN typeof = 'i' THEN price END) AS timp, SUM(CASE WHEN typeof = 'c' THEN price END) AS tclick FROM $log_table log $conditions $time_conditions ");

        $queryitems4 = $wpdb->get_results(" SELECT SUM(CASE WHEN typeof = 'i' THEN 1 END) AS imp, SUM(CASE WHEN typeof = 'c' THEN 1 END) AS click FROM $log_table log $conditions $time_conditions ");

        $queryitems5 = $wpdb->get_results(" SELECT SUM(CASE WHEN typeof = 'f' AND feedback = 1 THEN 1 END) AS feed1, SUM(CASE WHEN typeof = 'f' AND feedback = 2 THEN 1 END) AS feed2, SUM(CASE WHEN typeof = 'f' AND feedback = 3 THEN 1 END) AS feed3 FROM $log_table log $conditions $time_conditions ");

        $this->queryitems  = $queryitems;
        $this->queryitems2 = $queryitems2;
        $this->queryitems3 = $queryitems3;
        $this->queryitems4 = $queryitems4;
        $this->queryitems5 = $queryitems5;    
    
    }


    public function advert_analysis_screen_options(){
        
        ?>

       <script type="text/javascript">

        var analysisInfo = {

        <?php
    
        global $wpdb;
        $log_table = $wpdb->prefix . 'advert_logged';

        if(current_user_can('publish_adverts')){
        $active_advertiser  = get_posts(array('post_type' => 'advert-advertiser' , 'posts_per_page' => -1, 'post_status' => 'publish'));
        $archive_advertiser = get_posts(array('post_type' => 'advert-advertiser' , 'posts_per_page' => -1, 'post_status' => 'advert-archive'));
        }

        $active_campaign  = get_posts(array('post_type' => 'advert-campaign' , 'posts_per_page' => -1, 'post_status' => 'publish'));
        $archive_campaign = get_posts(array('post_type' => 'advert-campaign' , 'posts_per_page' => -1, 'post_status' => 'advert-archive'));
        $all_campaigns    = get_posts(array('post_type' => 'advert-campaign' , 'posts_per_page' => -1, 'post_status' => array('publish','advert-archive')));

        $active_banner  = get_posts(array('post_type' => 'advert-banner' , 'posts_per_page' => -1, 'post_status' => 'publish'));
        $archive_banner = get_posts(array('post_type' => 'advert-banner' , 'posts_per_page' => -1, 'post_status' => 'advert-archive'));


        if(current_user_can('publish_adverts')){

        foreach ($active_advertiser as $advertiser){
        $company = get_post_meta($advertiser->ID , 'advertiser_company' , true);
        $quickcheck = $wpdb->get_var(" SELECT adv_id FROM $log_table WHERE adv_id = $advertiser->ID ");
        if(!empty($quickcheck)){
        echo "advertiser".$advertiser->ID.": { ID: '".$advertiser->ID."', STATUS: 'active', NAME: '".$company."' },";
        }
        }

        foreach ($archive_advertiser as $advertiser){
        $company = get_post_meta($advertiser->ID , 'advertiser_company' , true);
        $quickcheck = $wpdb->get_var(" SELECT adv_id FROM $log_table WHERE adv_id = $advertiser->ID ");
        if(!empty($quickcheck)){
        echo "advertiser".$advertiser->ID.": { ID: '".$advertiser->ID."', STATUS: 'archive', NAME: '".$company."' },";
        }
        }

        //check for any other data not in wp_posts
        $adv_queryitems = $wpdb->get_results(" SELECT * FROM $log_table  ");
        $adv_check = array();
        foreach ($adv_queryitems as $adv){
        if ( get_post_status($adv->adv_id) != 'publish' && get_post_status($adv->adv_id) != 'advert-archive' && !in_array($adv->adv_id, $adv_check) ){
        echo "advertiser".$adv->adv_id.": { ID: '".$adv->adv_id."', STATUS: 'archive', NAME: '".$adv->adv_name."-".$adv->adv_id.__( '(Deleted)', 'ADVERT_TEXTDOMAIN' )."' },";
        array_push($adv_check, $adv->adv_id);
        }
        }

        }


        foreach ($active_campaign as $campaign){
        $campaign_owner = get_post_meta($campaign->ID , 'campaign_owner' ,true);
        $quickcheck = $wpdb->get_var(" SELECT camp_id FROM $log_table WHERE camp_id = $campaign->ID ");
        if(!empty($quickcheck)){
        echo "campaign".$campaign->ID.": { ID: '".$campaign->ID."', STATUS: 'active', NAME: '".$campaign->post_title."', AID: '".$campaign_owner."' },";
        }
        }

        foreach ($archive_campaign as $campaign){
        // i should be able to get the campaign owner from the current adv
        $campaign_owner = get_post_meta($campaign->ID , 'campaign_owner' ,true);
        $quickcheck = $wpdb->get_var(" SELECT camp_id FROM $log_table WHERE camp_id = $campaign->ID ");
        if(!empty($quickcheck)){
        echo "campaign".$campaign->ID.": { ID: '".$campaign->ID."', STATUS: 'archive', NAME: '".$campaign->post_title."', AID: '".$campaign_owner."' },";
        }
        }

        //check for any other data not in wp_posts
        if( current_user_can('publish_adverts') ){
        $camp_queryitems = $wpdb->get_results(" SELECT * FROM $log_table  ");
        $camp_check = array();
        foreach ($camp_queryitems as $camp){
        if ( get_post_status($camp->camp_id) != 'publish' && get_post_status($camp->camp_id) != 'advert-archive' && !in_array($camp->camp_id, $camp_check) ){
        echo "campaign".$camp->camp_id.": { ID: '".$camp->camp_id."', STATUS: 'archive', NAME: '".$camp->camp_name."-".$camp->camp_id.__( '(Deleted)', 'ADVERT_TEXTDOMAIN' )."', AID: '".$camp->adv_id."' },";
        array_push($camp_check, $camp->camp_id);
        }
        }
        }


        foreach ($active_banner as $banner){
        $banner_owner = get_post_meta($banner->ID , 'banner_owner' ,true);
        $banner_location = get_post_meta($banner->ID , 'banner_location' ,true);

        foreach ($all_campaigns as $campaign){
        $campaign_owner = get_post_meta($campaign->ID , 'campaign_owner' ,true);
        $campaign_location = get_post_meta($campaign->ID , 'campaign_location' ,false);
        if (in_array($banner_location, $campaign_location) && $banner_owner === $campaign_owner) {
        $quickcheck = $wpdb->get_var(" SELECT banner_id FROM $log_table WHERE banner_id = $banner->ID ");
        if(!empty($quickcheck)){
        echo "banner".$banner->ID.$campaign->ID.": { ID: '".$banner->ID."', STATUS: 'active', NAME: '".$banner->post_title."', AID: '".$campaign_owner."', CID: '".$campaign->ID."', CNAME: '".$campaign->post_title."' },";
        }
        }
        }
        }

        $campaign = '';

        foreach ($archive_banner as $banner){
        $banner_owner = get_post_meta($banner->ID , 'banner_owner' ,true);
        $banner_location = get_post_meta($banner->ID , 'banner_location' ,true);

        foreach ($all_campaigns as $campaign){
        $campaign_owner = get_post_meta($campaign->ID , 'campaign_owner' ,true);
        $campaign_location = get_post_meta($campaign->ID , 'campaign_location' ,false);
        if (in_array($banner_location, $campaign_location) && $banner_owner === $campaign_owner) {
        $quickcheck = $wpdb->get_var(" SELECT banner_id FROM $log_table WHERE banner_id = $banner->ID ");
        if(!empty($quickcheck)){
        echo "banner".$banner->ID.$campaign->ID.": { ID: '".$banner->ID."', STATUS: 'archive', NAME: '".$banner->post_title."', AID: '".$campaign_owner."', CID: '".$campaign->ID."', CNAME: '".$campaign->post_title."' },";
        }
        }
        }
        }

        //check for any other data not in wp_posts
        if( current_user_can('publish_adverts') ){
        $adb_queryitems = $wpdb->get_results(" SELECT * FROM $log_table  ");
        $adb_check = array();
        foreach ($adb_queryitems as $adb){
        if ( get_post_status($adb->banner_id) != 'publish' && get_post_status($adb->banner_id) != 'advert-archive' && !in_array($adb->camp_id, $adb_check) ){
        echo "banner".$adb->banner_id.$adb->camp_id.": { ID: '".$adb->banner_id."', STATUS: 'archive', NAME: '".$adb->banner_name."-".$adb->banner_id.__( '(Deleted)', 'ADVERT_TEXTDOMAIN' )."', AID: '".$adb->adv_id."', CID: '".$adb->camp_id."', CNAME: '".$adb->camp_id."' },";
        array_push($adb_check, $adb->camp_id);
        }
        }
        }

        ?>

        }

function onSelectChange(CHID) {
    if (CHID === 'start') {
        CHID = 'advert-analysis-status';
        s = ['any', 'advert-analysis-status'];
        a = ['all_advertisers', 'advert-analysis-advertiser'];
        c = ['all_campaigns', 'advert-analysis-campaign'];
    } else {
        var s = document.getElementById('advert-analysis-status');
        s = [s.options[s.selectedIndex].value, 'advert-analysis-status'];
        var a = document.getElementById('advert-analysis-advertiser');
        a = [a.options[a.selectedIndex].value, 'advert-analysis-advertiser'];
        var c = document.getElementById('advert-analysis-campaign');
        c = [c.options[c.selectedIndex].value, 'advert-analysis-campaign'];
        var ad = document.getElementById('advert-analysis-banner');
        ad = [ad.options[ad.selectedIndex].value, 'advert-analysis-banner'];
    }
    changeValues(s, a, c, ad, CHID);
}
function changeValues(s, a, c, ad, CHID) {
    var newOption;
    var count;
    var previous = [];
    var chkActive;
    var chkArchive;
    var ad2camp;
    var advCountActive = 0;
    var advCountArchive = 0;
    var campCountActive = 0;
    var campCountArchive = 0;
    var adCountActive = 0;
    var adCountArchive = 0;
    if (s[0] === 'any') {
        chkActive = 'active';
        chkArchive = 'archive';
    } else if (s[0] === 'active') {
        chkActive = 'active';
        chkArchive = 'active';
    } else if (s[0] === 'archive') {
        chkActive = 'archive';
        chkArchive = 'archive';
    }
    if (CHID === 'advert-analysis-status') {
        count = 0;
        newOption = '';
        for (var key in analysisInfo) {
            if (key.indexOf("advertiser") > -1) {
                if (analysisInfo[key].STATUS === chkActive || analysisInfo[key].STATUS === chkArchive) {
                    newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                    if (analysisInfo[key].STATUS === chkActive) {
                        advCountActive = 1;
                    }
                    if (analysisInfo[key].STATUS === chkArchive) {
                        advCountArchive = 1;
                    }
                    count++;
                }
            }
        }
        if (count > 0) {
            document.getElementById('advert-analysis-advertiser').disabled = false;
            newOption = '<option value="">All Advertisers</option>' + newOption;
            document.getElementById('advert-analysis-advertiser').innerHTML = newOption;
        } else {
            document.getElementById('advert-analysis-advertiser').selectedIndex = 0;
            document.getElementById('advert-analysis-advertiser').disabled = true;
        }
        document.getElementById('advert-analysis-advertiser').selectedIndex = 0;
    }
    if (CHID === 'advert-analysis-status' || CHID === 'advert-analysis-advertiser') {
        if (a[0] != 'all_advertisers') {
            ad2camp = a[0];
        } else {
            ad2camp = 0;
        }
        count = 0;
        newOption = '';
        for (var key in analysisInfo) {
            if (key.indexOf("campaign") > -1) {
                if (ad2camp > 0) {
                    if (analysisInfo[key].STATUS === chkActive && analysisInfo[key].AID === ad2camp || analysisInfo[key].STATUS === chkArchive && analysisInfo[key].AID === ad2camp) {
                        newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                        if (analysisInfo[key].STATUS === chkActive) {
                            campCountActive = 1;
                        }
                        if (analysisInfo[key].STATUS === chkArchive) {
                            campCountArchive = 1;
                        }
                        count++;
                    }
                } else {
                    if (analysisInfo[key].STATUS === chkActive || analysisInfo[key].STATUS === chkArchive) {
                        newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                        if (analysisInfo[key].STATUS === chkActive) {
                            campCountActive = 1;
                        }
                        if (analysisInfo[key].STATUS === chkArchive) {
                            campCountArchive = 1;
                        }
                        count++;
                    }
                }
            }
        }
        if (count > 0) {
            document.getElementById('advert-analysis-campaign').disabled = false;
            newOption = '<option value="">All Campaigns</option>' + newOption;
            document.getElementById('advert-analysis-campaign').innerHTML = newOption;
        } else {
            document.getElementById('advert-analysis-campaign').selectedIndex = 0;
            document.getElementById('advert-analysis-campaign').disabled = true;
        }
        document.getElementById('advert-analysis-campaign').selectedIndex = 0;
    }
    if (CHID != 'advert-analysis-banner') {
        if (a[0] != 'all_campaigns') {
            camp2banner = c[0];
        } else {
            camp2banner = 0;
        }
        ad2camp = a[0];
        count = 0;
        newOption = '';
        for (var key in analysisInfo) {
            if (key.indexOf("banner") > -1) {
                if (camp2banner > 0) {
                    if (analysisInfo[key].STATUS === chkActive && analysisInfo[key].CID === camp2banner || analysisInfo[key].STATUS === chkArchive && analysisInfo[key].CID === camp2banner) {
                        if (previous.indexOf(analysisInfo[key].ID) === -1) {
                            newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                            if (analysisInfo[key].STATUS === chkActive) {
                                adCountActive = 1;
                            }
                            if (analysisInfo[key].STATUS === chkArchive) {
                                adCountArchive = 1;
                            }
                            previous.push(analysisInfo[key].ID);
                            count++;
                        }
                    }
                } else if (ad2camp > 0) {
                    if (analysisInfo[key].STATUS === chkActive && analysisInfo[key].AID === ad2camp || analysisInfo[key].STATUS === chkArchive && analysisInfo[key].AID === ad2camp) {
                        if (previous.indexOf(analysisInfo[key].ID) === -1) {
                            newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                            if (analysisInfo[key].STATUS === chkActive) {
                                adCountActive = 1;
                            }
                            if (analysisInfo[key].STATUS === chkArchive) {
                                adCountArchive = 1;
                            }
                            previous.push(analysisInfo[key].ID);
                            count++;
                        }
                    }
                } else {
                    if (analysisInfo[key].STATUS === chkActive || analysisInfo[key].STATUS === chkArchive) {
                        if (previous.indexOf(analysisInfo[key].ID) === -1) {
                            newOption = newOption + '<option value="' + analysisInfo[key].ID + '">' + analysisInfo[key].NAME + '</option>';
                            if (analysisInfo[key].STATUS === chkActive) {
                                adCountActive = 1;
                            }
                            if (analysisInfo[key].STATUS === chkArchive) {
                                adCountArchive = 1;
                            }
                            previous.push(analysisInfo[key].ID);
                            count++;
                        }
                    }
                }
            }
        }
        if (count > 0) {
            document.getElementById('advert-analysis-banner').disabled = false;
            newOption = '<option value="">All Banners</option>' + newOption;
            document.getElementById('advert-analysis-banner').innerHTML = newOption;
        } else {
            document.getElementById('advert-analysis-banner').selectedIndex = 0;
            document.getElementById('advert-analysis-banner').disabled = true;
        }
    }


    if (s[0] != 'any') {
        if (document.getElementById('advert-analysis-advertiser').disabled === true) {
        } else {
            document.getElementById('advert-analysis-advertiser').required = true;
        }
        if (document.getElementById('advert-analysis-campaign').disabled === true) {
        } else {
            document.getElementById('advert-analysis-campaign').required = true;
        }
        if (document.getElementById('advert-analysis-banner').disabled === true) {
        } else {
            document.getElementById('advert-analysis-banner').required = true;
        }
        if (a[0] > 0 && document.getElementById('advert-analysis-advertiser').disabled != true) {
            document.getElementById('advert-analysis-campaign').required = false;
            document.getElementById('advert-analysis-banner').required = false;
        }
        if (c[0] > 0) {
            document.getElementById('advert-analysis-advertiser').required = false;
            document.getElementById('advert-analysis-banner').required = false;
        }
        if (ad[0] > 0) {
            document.getElementById('advert-analysis-advertiser').required = false;
            document.getElementById('advert-analysis-campaign').required = false;
        }
    } else {
        document.getElementById('advert-analysis-advertiser').required = false;
        document.getElementById('advert-analysis-campaign').required = false;
        document.getElementById('advert-analysis-banner').required = false;
    }
}

        </script>


        <div id="advert-analysis-drilldown-meta-prefs-workaround" class="hide-if-no-js">

		        <h5><?php _e( 'Analysis Options', 'ADVERT_TEXTDOMAIN' ); ?></h5>

		        <form id="advert-analysis-settings" action="<?php echo esc_url(admin_url('admin.php?page=advert-analysis-drilldown')); ?>" method="post">

                <?php wp_nonce_field( 'admin_analysis', 'admin-analysis' ); ?>
                <?php wp_get_referer() ?>
                <input type="hidden" id="hiddenaction" name="action" value="adminanalysissubmit" />
                <input type="hidden" id="originalaction" name="originalaction" value="adminanalysis" />
                <input type="hidden" id="post_type" name="post_type" value="admin_analysis_options" />

                <div class="advert-select-wrap">
                <label for="advert-analysis-status"><?php _e( 'Status', 'ADVERT_TEXTDOMAIN' ); ?></label>
                <select id="advert-analysis-status" name="advert-analysis-status" onChange="onSelectChange(this.id)" >
                <option class="advert-any" value="any" selected><?php _e( 'Any', 'ADVERT_TEXTDOMAIN' ); ?></option>
                <option class="advert-active" value="active"><?php _e( 'Active', 'ADVERT_TEXTDOMAIN' ); ?></option>
                <option class="advert-archive" value="archive"><?php _e( 'Archived', 'ADVERT_TEXTDOMAIN' ); ?></option>
                </select>
                </div>

                <?php

                if(current_user_can('publish_adverts')){

                $active_location = get_posts(array('post_type' => 'advert-location' , 'posts_per_page' => -1, 'post_status' => array('publish','archive') ));

                echo '<div class="advert-select-wrap">';
                echo '<label for="advert-analysis-location">' . __( 'Location', 'ADVERT_TEXTDOMAIN' ) . '</label>';
                echo '<select id="advert-analysis-location" name="advert-analysis-location">';
                echo '<option value="all_locations" selected>' . __( 'All Locations', 'ADVERT_TEXTDOMAIN' ) . '</option>';

                foreach ($active_location as $location){
                $quickcheck = $wpdb->get_var(" SELECT location_id FROM $log_table WHERE location_id = $location->ID ");
                if(!empty($quickcheck)){
                echo '<option value="' . $location->ID . '">' . $location->post_title . '</option>';
                }
                }

                //check for any other data not in wp_posts
                $loc_queryitems = $wpdb->get_results(" SELECT * FROM $log_table ");
                $loc_check = array();
                foreach ($loc_queryitems as $loc){
                if ( get_post_status($loc->location_id) != 'publish' && !in_array($loc->location_id, $loc_check) ){
                echo '<option value="' . $loc->ID . '">' . $loc->location_name . '-' . $loc->ID . __( '(Deleted)', 'ADVERT_TEXTDOMAIN' ) . '</option>';
                }
                }

                echo '</select>';
                echo '</div>';

                }
            
                if(!current_user_can('publish_adverts')){
                    $user_id    = get_current_user_id();
                    $company_id = get_user_meta($user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
                    $company    = get_post_meta($company_id, 'advertiser_company' , true);
                    echo '<div class="advert-select-wrap" style="display:none;">';
                    echo '<label for="advert-analysis-advertiser">' . __( 'Advertiser', 'ADVERT_TEXTDOMAIN' ) . '</label>';
                    echo '<select id="advert-analysis-advertiser" name="advert-analysis-advertiser">';
                    echo '<option value="'.$company_id.'" selected>'.$company.'</option>';
                }
                else{
                    echo '<div class="advert-select-wrap">';
                    echo '<label for="advert-analysis-advertiser">' . __( 'Advertiser', 'ADVERT_TEXTDOMAIN' ) . '</label>';
                    echo '<select id="advert-analysis-advertiser" name="advert-analysis-advertiser" required onChange="onSelectChange(this.id)" >';
                    echo '<option value="all_advertisers" selected>'. __( 'All Advertisers', 'ADVERT_TEXTDOMAIN' ) .'</option>';
                }
                ?>
                </select>
                </div>

                <div class="advert-select-wrap">
                <label for="advert-analysis-campaign"><?php _e( 'Campaign', 'ADVERT_TEXTDOMAIN' ); ?></label>
                <select id="advert-analysis-campaign" name="advert-analysis-campaign" onChange="onSelectChange(this.id)" >
                <option value="all_campaigns" selected><?php _e( 'All Campaigns', 'ADVERT_TEXTDOMAIN' ); ?></option>
                </select>
                </div>

                <div class="advert-select-wrap">
                <label for="advert-analysis-banner"><?php _e( 'Banner', 'ADVERT_TEXTDOMAIN' ); ?></label>
                <select id="advert-analysis-banner" name="advert-analysis-banner" >
                <option value="all_banners" selected><?php _e( 'All Banners', 'ADVERT_TEXTDOMAIN' ); ?></option>
                </select>
                </div>


                <div class="advert-select-wrap">
                <label for="advert-analysis-start"><?php _e( 'Start Date', 'ADVERT_TEXTDOMAIN' ); ?></label>
                <input type="text" name="advert-analysis-start" class="adv_datetimepicker" id="advert-analysis-start" placeholder="<?php _e( 'mm/dd/yyyy' , 'ADVERT_TEXTDOMAIN' ); ?>" />
                </div>

                <div class="advert-select-wrap">
                <label for="advert-analysis-stop"><?php _e( 'Stop Date', 'ADVERT_TEXTDOMAIN' ); ?></label>
                <input type="text" name="advert-analysis-stop" class="adv_datetimepicker" id="advert-analysis-stop" placeholder="<?php _e( 'mm/dd/yyyy' , 'ADVERT_TEXTDOMAIN' ); ?>" />
                </div>

                <div class="advert-submit">
                <input type="submit" id="analysis-options-submit" class="button button-primary button-large" value="<?php _e( 'Submit', 'ADVERT_TEXTDOMAIN' ); ?>">
                </div>

		        </form>
        </div>

    <?php

    }
    

    public function do_advert_analysis_drilldown_page(){

        //get page
        $tab1 = esc_url(admin_url( 'admin.php?page=advert-analysis-overview' ));
        $tab2 = esc_url(admin_url( 'admin.php?page=advert-analysis-drilldown' ));

        $queryitems  = $this->queryitems;
        $queryitems2 = $this->queryitems2;
        $queryitems3 = $this->queryitems3;
        $queryitems4 = $this->queryitems4;
        $queryitems5 = $this->queryitems5;

        $status     = $this->status;
        $advertiser = $this->advertiser;
        $campaign   = $this->campaign;
        $banner    = $this->banner;
        $start      = $this->start;
        $stop       = $this->stop;
        $location   = $this->location;


        if(!empty($queryitems2)){

        ?>


            <script type="text/javascript" src="https://www.google.com/jsapi"></script>
            <script type="text/javascript">

              // Load the Visualization API and the piechart package.
              google.load('visualization', '1.0', {'packages':['corechart']});

              // Set a callback to run when the Google Visualization API is loaded.
              google.setOnLoadCallback(drawChart1);

              // Callback that creates and populates a data table,
              // instantiates the pie chart, passes in the data and
              // draws it.
              function drawChart1() {
                var data = google.visualization.arrayToDataTable([

                  ['Date', 'Impressions', 'Clicks'],      
                  <?php 
                  foreach ( $queryitems2 as $queryitem ){
                  if(strpos($queryitem->timestamp, ':') !== false){$dateformat = date("g:i a", strtotime($queryitem->timestamp));}
                  else{
                      $current_year = date('Y');
                      $timestamp_year = date('Y', strtotime($queryitem->timestamp));
                      if($timestamp_year < $current_year){$dateformat = date("M d y", strtotime($queryitem->timestamp));}
                      else{$dateformat = date("M d", strtotime($queryitem->timestamp));}
                      }
                  echo "['".$dateformat."', ".$queryitem->imp.", ".$queryitem->click."],";
                  }            
                  ?>
                ]);

                var options = {
                  vAxis:{minValue: 0, textPosition: 'in'},
                  backgroundColor:'#FAFAFA',
                  chartArea:{'width':'95%', 'height':'80%'},
                  legend:'none',
                  lineWidth:5,
                  pointSize:7,
                  pointShape:'circle'
                };

                var chart = new google.visualization.AreaChart(document.getElementById('chart_div1'));
                chart.draw(data, options);
              }

            </script>

        <?php } ?>


        <div class="wrap theme-settings-wrap">

        <div class="advert-page-heading-logo">a</div>

        <h1 class="advert-heading-tag"><?php _e( 'Analysis', 'ADVERT_TEXTDOMAIN' ); ?></h1>

        <div class="wrap advert-analysis-wrap" class="wrap">

        <div class="advert-tabbed">
        <a href="<?php echo $tab1; ?>"><h2><?php _e( 'Overview', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        <a href="<?php echo $tab2; ?>"><h2 class="advert-tab-active"><?php _e( 'Drilldown', 'ADVERT_TEXTDOMAIN' ); ?></h2></a>
        </div>
        <div class="clear"></div>

        <div class="advert-inner-wrap">

        <div class="advert-lookup">

        <?php
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] || isset($_GET['post']) ){

            $advertiser_name = $advertiser;
            $campaign_name   = $campaign;
            $banner_name    = $banner;
            $location_name   = $location; 

            $currentViewing = '';

            if(!empty($location)){
            if($location > 0){ if(get_the_title($location)){$location = get_the_title($location);} }else{$location = 'All Locations';}
            $currentViewing = '&nbsp;>&nbsp;'. __( 'Location', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;' . $location;
            }

            if(!empty($stop)){
            $currentViewing =  __( '&nbsp;to&nbsp;', 'ADVERT_TEXTDOMAIN' ) . $stop . $currentViewing;
            }

            if(!empty($start)){
            $currentViewing = '&nbsp;>&nbsp;'. __( 'from', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;' . $start . $currentViewing;
            }

            if(!empty($banner)){
            if($banner > 0){ if(get_the_title($banner)){$banner_name = get_the_title($banner);} }else{$banner_name = 'All Banners';}
            $currentViewing = '&nbsp;>&nbsp;'. __( 'Banner', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;' . $banner_name . $currentViewing;
            }

            if(!empty($campaign)){
            if($campaign > 0){ if(get_the_title($campaign)){$campaign_name = get_the_title($campaign);} }else{$campaign_name = 'All Campaigns';}
            $currentViewing = '&nbsp;>&nbsp;'. __( 'Campaign', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;' . $campaign_name . $currentViewing;
            }

            if(!empty($advertiser)){
            if($advertiser > 0){ if(get_the_title($advertiser)){$advertiser_name = get_the_title($advertiser);} }else{$advertiser_name = 'All Advertisers';}
            $currentViewing = '&nbsp;>&nbsp;'. __( 'Advertiser', 'ADVERT_TEXTDOMAIN' ) .':&nbsp;' . $advertiser_name . $currentViewing;
            }

            if(!empty($status)){
            $timespan = "";
            if(empty($start) && empty($stop)){$timespan = __( '&nbsp;> Past 24 hours', 'ADVERT_TEXTDOMAIN' );}
            $currentViewing = __( 'Status', 'ADVERT_TEXTDOMAIN' ) . ':&nbsp;' . ucfirst($status) . $currentViewing . $timespan;
            }
            else{
            $timespan = __( '&nbsp;> Past 24 hours', 'ADVERT_TEXTDOMAIN' );
            $currentViewing = __( 'Status', 'ADVERT_TEXTDOMAIN' ) . ':&nbsp;' . __( 'Any', 'ADVERT_TEXTDOMAIN' ) . $currentViewing . $timespan;    
            }
            echo '<h5>'.$currentViewing.'</h5>';

        }
        else{

        if(!current_user_can('publish_adverts')){
        $user_id = get_current_user_id();
        $company_id = get_user_meta($user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
        $advertiser = $company_id;
        echo '<h5>'. sprintf( __( 'Status: Any > Advertiser : %1$s > Past 24 hours', 'ADVERT_TEXTDOMAIN' ), get_the_title($advertiser) ) .'</h5>'; 
        }

        else{
        echo '<h5>'. __( 'Status: Any > Past 24 hours', 'ADVERT_TEXTDOMAIN' ) .'</h5>';    
        }

        }

        ?>

        </div>


        <div class="advert-inner-wrap-centered">
        <noscript><?php _e( 'Enable JavaScript for additional options', 'ADVERT_TEXTDOMAIN' ); ?></noscript>

        <?php 
 
        if(empty($queryitems2)){echo '<h1>'. _e( 'Nothing found', 'ADVERT_TEXTDOMAIN' ) .'</h1>';} 

        else{

        ?>

        <div id="chart_div1" class="advert-trans40" style="max-width:1177px;margin:0 auto"></div>

        <div class="advert-analysis-bl3">
        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Total Impressions', 'ADVERT_TEXTDOMAIN' ); ?></div>
        <div class="advert-analysis-overview-body">
        <?php
        if(!empty($queryitems4[0]->imp)){echo number_format_i18n($queryitems4[0]->imp);}else{echo '0';}
        ?>
        </div>
        </div>

        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Total Clicks', 'ADVERT_TEXTDOMAIN' ); ?></div>
        <div class="advert-analysis-overview-body">
        <?php
        if(!empty($queryitems4[0]->click)){echo number_format_i18n($queryitems4[0]->click);}else{echo '0';}
        ?>
        </div>
        </div>

        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Clickthrough Rate', 'ADVERT_TEXTDOMAIN' ); ?></div>
        <div class="advert-analysis-overview-body">
        <?php
        if(!empty($queryitems4[0]->click)){echo number_format_i18n(($queryitems4[0]->click / $queryitems4[0]->imp) * 100, 2).'%';}else{echo '0%';}
        ?>
        </div>
        </div>
        </div>


        <div class="advert-analysis-bl2">
        <div class="advert-analysis-overview-wrap bl2">
        <div class="advert-analysis-overview-head"><?php if(!current_user_can('publish_adverts')){_e( 'Total Charged from Impressions', 'ADVERT_TEXTDOMAIN' );}else{_e( 'Total Earned from Impressions', 'ADVERT_TEXTDOMAIN' );}?></div>
        <div class="advert-analysis-overview-body">
        <?php
        echo number_format_i18n($queryitems3[0]->timp, 2);
        ?>
        </div>
        </div>

        <div class="advert-analysis-overview-wrap bl2">
        <div class="advert-analysis-overview-head"><?php if(!current_user_can('publish_adverts')){_e( 'Total Charged from Clicks', 'ADVERT_TEXTDOMAIN' );}else{_e( 'Total Earned from Clicks', 'ADVERT_TEXTDOMAIN' );}?></div>
        <div class="advert-analysis-overview-body">
        <?php
        echo number_format_i18n($queryitems3[0]->tclick, 2);
        ?>
        </div>
        </div>
        </div>

        <?php

        $reorder          = '';
        $status_order     = '';
        $advertiser_order = '';
        $campaign_order   = '';
        $banner_order    = '';
        $start_order      = '';
        $stop_order       = '';
        $location_order   = '';

        if ( current_user_can('edit_adverts') ){

        //status
        if(!empty($status)){
        $status_order = '&amp;advert-analysis-status='.$status;    
        }

        //advertiser
        if(!current_user_can('publish_adverts')){
        $user_id = get_current_user_id();
        $company_id = get_user_meta($user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);
        $advertiser_order = '&amp;advert-analysis-advertiser='.$company_id;
        }
        else{
        if(!empty($advertiser)){
        $advertiser_order = '&amp;advert-analysis-advertiser='.$advertiser;
        }
        }

        //campaign
        if(!empty($campaign)){
        $campaign_order = '&amp;advert-analysis-campaign='.$campaign;    
        }

        //banner
        if(!empty($banner)){
        $banner_order = '&amp;advert-analysis-banner='.$banner;    
        }

        //start
        if(!empty($start)){
        $start_order = '&amp;advert-analysis-start='.urldecode($start);    
        }

        //stop
        if(!empty($stop)){
        $stop_order = '&amp;advert-analysis-stop='.urldecode($stop);    
        }

        //location
        if(!empty($location)){
        $location_order = '&amp;advert-analysis-location='.$location; 
        }

        if( !empty($advertiser) || !empty($campaign) || !empty($banner) || !empty($start) || !empty($stop) || !empty($location) ){
        $nonce = wp_create_nonce( 'advert-analysis-link' );
        $reorder = '&amp;post=analysis'.$status_order.$advertiser_order.$campaign_order.$banner_order.$start_order.$stop_order.$location_order.'&amp;_wpnonce='.$nonce;
        }

        }

        ?>


        <div class="advert-analysis-everything">

        <div class="advert-analysis-everything-fixed-head">
        <table cellspacing="0" id="aae-table-header">
        <thead>
        <tr>
        <?php 

        if(isset($_GET['order']) && isset($_GET['orderby'])) {
        $order = sanitize_text_field($_GET['order']);
        $orderby = sanitize_text_field($_GET['orderby']); 

        ?>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=ip_address&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'ADDRESS', 'ADVERT_TEXTDOMAIN'); if($orderby == 'ip_address'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=browser&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'BROWSER', 'ADVERT_TEXTDOMAIN'); if($orderby == 'browser'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=device&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'DEVICE', 'ADVERT_TEXTDOMAIN'); if($orderby == 'device'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=typeof&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'AD LOG', 'ADVERT_TEXTDOMAIN'); if($orderby == 'typeof'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=location_id&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'LOCATION', 'ADVERT_TEXTDOMAIN'); if($orderby == 'location'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=camp_id&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'CAMP ID', 'ADVERT_TEXTDOMAIN'); if($orderby == 'camp_id'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=banner_id&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'BANNER ID', 'ADVERT_TEXTDOMAIN'); if($orderby == 'banner_id'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=adv_id&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'ADVERT ID', 'ADVERT_TEXTDOMAIN'); if($orderby == 'adv_id'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=price&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'PRICE', 'ADVERT_TEXTDOMAIN'); if($orderby == 'price'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=time&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'TIME', 'ADVERT_TEXTDOMAIN'); if($orderby == 'time'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <th width="182px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=url&order=<?php if($order == 'asc'){echo 'desc';}elseif($order == 'desc'){echo 'asc';}else{echo 'asc';}?>"><?php _e( 'URL', 'ADVERT_TEXTDOMAIN'); if($orderby == 'url'){if($order == 'asc'){echo '<br />&uarr;';}else{echo '<br />&darr;';}}?></a></th>
        <?php } else{ ?>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=ip_address&order=asc"><?php _e( 'ADDRESS', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=browser&order=asc"><?php _e( 'BROWSER', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=device&order=asc"><?php _e( 'DEVICE', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=typeof&order=asc"><?php _e( 'AD LOG', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=location_id&order=asc"><?php _e( 'LOCATION', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=camp_id&order=asc"><?php _e( 'CAMP ID', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=banner_id&order=asc"><?php _e( 'BANNER ID', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=adv_id&order=asc"><?php _e( 'ADVERT ID', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="62px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=price&order=asc"><?php _e( 'PRICE', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="150px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=time&order=asc"><?php _e( 'TIME', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <th width="182px"><a href="admin.php?page=advert-analysis-drilldown<?php echo $reorder;?>&orderby=url&order=asc"><?php _e( 'URL', 'ADVERT_TEXTDOMAIN'); ?></a></th>
        <?php } ?>
        </tr>
        </thead>
        </table>
        </div>

        <div class="advert-analysis-everything-wrap">
        <table cellspacing="0" id="aae-table">
        <thead>
        <tr>
        <th width="150px"><?php _e( 'ADDRESS', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="150px"><?php _e( 'BROWSER', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="150px"><?php _e( 'DEVICE', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'AD LOG', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'LOCATION', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'CAMP ID', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'BANNER ID', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'ADVERT ID', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="62px"><?php _e( 'PRICE', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="150px"><?php _e( 'TIME', 'ADVERT_TEXTDOMAIN'); ?></th>
        <th width="180px"><?php _e( 'URL', 'ADVERT_TEXTDOMAIN'); ?></th>
        </tr>
        </thead>

        <tbody style="text-align:center">

        <?php

        foreach ( $queryitems as $queryitem ){

            if($queryitem->typeof === 'f'){
                echo '<tr class="adv-analysis-everything-highlight"><td title="'.$queryitem->ip_address.'">'.$queryitem->ip_address.'</td><td title="'.$queryitem->browser.'">'.$queryitem->browser.'</td><td title="'.$queryitem->device.'">'.$queryitem->device.'</td><td title="'.$queryitem->typeof.'-'.$queryitem->feedback.'">'.$queryitem->typeof.'-'.$queryitem->feedback.'</td><td title="'.$queryitem->location_id.'">'.$queryitem->location_id.'</td><td title="'.$queryitem->camp_id.'">'.$queryitem->camp_id.'</td><td title="'.$queryitem->banner_id.'">'.$queryitem->banner_id.'</td><td title="'.$queryitem->adv_id.'">'.$queryitem->adv_id.'</td><td title="'.number_format_i18n($queryitem->price).'">'.number_format_i18n($queryitem->price).'</td><td title="'.$queryitem->time.'">'.$queryitem->time.'</td><td title="'.$queryitem->url.'">'.$queryitem->url.'</td></tr>';    
            }
            else{
                echo '<tr class="adv-analysis-everything-highlight"><td title="'.$queryitem->ip_address.'">'.$queryitem->ip_address.'</td><td title="'.$queryitem->browser.'">'.$queryitem->browser.'</td><td title="'.$queryitem->device.'">'.$queryitem->device.'</td><td title="'.$queryitem->typeof.'">'.$queryitem->typeof.'</td><td title="'.$queryitem->location_id.'">'.$queryitem->location_id.'</td><td title="'.$queryitem->camp_id.'">'.$queryitem->camp_id.'</td><td title="'.$queryitem->banner_id.'">'.$queryitem->banner_id.'</td><td title="'.$queryitem->adv_id.'">'.$queryitem->adv_id.'</td><td title="'.number_format_i18n($queryitem->price).'">'.number_format_i18n($queryitem->price).'</td><td title="'.$queryitem->time.'">'.$queryitem->time.'</td><td title="'.$queryitem->url.'">'.$queryitem->url.'</td></tr>';
            }

        }

        ?>

        </tbody>
        </table>
        </div>

        </div>

        <div class="advert-analysis-legend advert-sm-info"><span><?php _e( 'i = Impression, c = Click, f-1 = Feedback Relevant, f-2 = Feedback Not Relevant, f-3 = Feedback Displayed too much, Price = Amount of AdCredits charged', 'ADVERT_TEXTDOMAIN'); ?></span></div>

        <div class="advert-raw-data hide-if-no-js"><p><a href="#" class="advert-open-raw-data"><?php _e( 'View Data in new window', 'ADVERT_TEXTDOMAIN'); ?></a>&nbsp;|&nbsp;<a href="#" class="advert-export-csv"><?php _e( 'Export Data to CSV', 'ADVERT_TEXTDOMAIN'); ?></a><p></div>

        <div class="advert-label"><?php _e( 'User Feedback', 'ADVERT_TEXTDOMAIN'); ?></div>

        <div class="advert-analysis-bl3">
        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Relevant', 'ADVERT_TEXTDOMAIN'); ?></div>
        <div class="advert-analysis-overview-body"><?php echo number_format_i18n($queryitems5[0]->feed1); ?></div>
        </div>

        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Not Relevant', 'ADVERT_TEXTDOMAIN'); ?></div>
        <div class="advert-analysis-overview-body"><?php echo number_format_i18n($queryitems5[0]->feed2); ?></div>
        </div>

        <div class="advert-analysis-overview-wrap bl3">
        <div class="advert-analysis-overview-head"><?php _e( 'Displayed too much', 'ADVERT_TEXTDOMAIN'); ?></div>
        <div class="advert-analysis-overview-body"><?php echo number_format_i18n($queryitems5[0]->feed3); ?></div>
        </div>
        </div>

        <?php } ?>
        </div>

        </div>

        </div>

        </div>

        <script>
        window.onload = onSelectChange('start');
        </script>

        <?php
    
    }
    

}// End AdVert Analysis Drilldown Class