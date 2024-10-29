<?php

class AdVert_Analysis {


    protected $queryitems;


    public function __construct() {

        $this->advert_analysis_filter_option();
        $this->do_analysis_page();

    }

    public function advert_analysis_filter_option(){

        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && $_POST['post_type'] == 'admin_analysis_filter'  && $_POST['originalaction'] == 'adminanalysis' && current_user_can('edit_adverts') ){
        
            if ( !is_user_logged_in() || ! wp_verify_nonce( $_POST['admin-analysis'], 'admin_analysis' ) ){
                print 'Woah, whats really going on...?';
                return;
            }

            $filter = $_POST['advert-analysis-filter'];

            if(!empty($filter))
            $this->get_filter_data($filter);

        }
        else{
            if( current_user_can('edit_adverts') ){
                $filter = 'today';
                $this->get_filter_data($filter);
            }
        }

    }


    public function get_filter_data($filter){

        global $wpdb;
        $log_table = $wpdb->prefix . 'advert_logged';
        $current_year = date('Y');
        $current_date = current_time('Y-m-d');

        if($filter === 'today'){$time_conditions = " WHERE DATE(time) = '{$current_date}' ";}
        if($filter === 'yesterday'){$time_conditions = " WHERE DATE(time) = DATE_SUB('{$current_date}', INTERVAL 1 DAY) ";}
        if($filter === 'pastweek'){$time_conditions = " WHERE DATE(time) >= DATE_SUB('{$current_date}', INTERVAL 1 WEEK) ";}
        if($filter === 'pastmonth'){$time_conditions = " WHERE DATE(time) >= DATE_SUB('{$current_date}', INTERVAL 1 MONTH) ";}
        if($filter === 'pastyear'){$time_conditions = " WHERE DATE(time) >= DATE_SUB('{$current_date}', INTERVAL 1 YEAR) ";}
        if($filter === 'ytd'){$time_conditions = " WHERE YEAR(time) = $current_year ";}

        if(!current_user_can('publish_adverts')){

            $user_id    = get_current_user_id();
            $company_id = get_user_meta( $user_id, 'advert_advertiser_company_id'.get_current_blog_id(), true);

            if($filter == 'all'){
                $advert_user = " WHERE adv_id = $company_id ";
            }
            else{
                $advert_user = " AND adv_id = $company_id ";
            }

        }
        else{
            $advert_user = "";   
        }

        if($filter == 'all'){
            $queryitems = $wpdb->get_results(" SELECT SUM(price) AS total, COUNT(CASE WHEN typeof = 'i' THEN 1 END) AS imp, COUNT(CASE WHEN typeof = 'c' THEN 1 END) click  FROM $log_table $advert_user ");
        }
        elseif(!empty($filter)){
            $queryitems = $wpdb->get_results(" SELECT SUM(price) AS total, COUNT(CASE WHEN typeof = 'i' THEN 1 END) AS imp, COUNT(CASE WHEN typeof = 'c' THEN 1 END) click FROM $log_table $time_conditions $advert_user ");
        }

        $this->queryitems = $queryitems;

    }


    public function do_analysis_page(){

        $queryitems = $this->queryitems;

        //get page
        $tab1 = esc_url(admin_url('admin.php?page=advert-analysis-overview'));
        $tab2 = esc_url(admin_url('admin.php?page=advert-analysis-drilldown'));

        ?>

        <div class="wrap theme-settings-wrap">

            <div class="advert-page-heading-logo">a</div>

            <h1 class="advert-heading-tag"><?php _e('Analysis', 'ADVERT_TEXTDOMAIN');?></h1>

            <div class="wrap advert-analysis-wrap" class="wrap">

                <div class="advert-tabbed">
                    <a href="<?php echo $tab1; ?>"><h2 class="advert-tab-active"><?php _e('Overview', 'ADVERT_TEXTDOMAIN');?></h2></a>
                    <a href="<?php echo $tab2; ?>"><h2><?php _e('Drilldown', 'ADVERT_TEXTDOMAIN');?></h2></a>
                </div>
                <div class="clear"></div>

                <div class="advert-inner-wrap">
                    <div class="advert-select-wrap fright">
                    <form id="advert-analysis-overview-filter" action="" method="post">

                    <?php wp_nonce_field( 'admin_analysis', 'admin-analysis' ); ?>
                    <?php wp_get_referer() ?>
                    <input type="hidden" id="hiddenaction" name="action" value="adminanalysisfilter" />
                    <input type="hidden" id="originalaction" name="originalaction" value="adminanalysis" />
                    <input type="hidden" id="post_type" name="post_type" value="admin_analysis_filter" />

                    <?php if(isset($_POST['advert-analysis-filter'])){$filtered = $_POST['advert-analysis-filter'];}else{$filtered = 'today';}
 
                    ?>

                    <select id="advert-analysis-filter" name="advert-analysis-filter">
                    <option value="today" <?php if($filtered === 'today' ){echo 'selected="selected"';}?> ><?php _e('Today', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="yesterday" <?php if($filtered === 'yesterday'){echo 'selected="selected"';}?> ><?php _e('Yesterday', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="pastweek" <?php if($filtered === 'pastweek'){echo 'selected="selected"';}?> ><?php _e('Past Week', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="pastmonth" <?php if($filtered === 'pastmonth'){echo 'selected="selected"';}?> ><?php _e('Past Month', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="ytd" <?php if($filtered === 'ytd'){echo 'selected="selected"';}?> ><?php _e('Year to Date', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="pastyear" <?php if($filtered === 'pastyear'){echo 'selected="selected"';}?> ><?php _e('Past Year', 'ADVERT_TEXTDOMAIN');?></option>
                    <option value="all" <?php if($filtered === 'all'){echo 'selected="selected"';}?> ><?php _e('All', 'ADVERT_TEXTDOMAIN');?></option>
                    </select>
                    <input type="submit" name="filter_action" id="advert-overview-query-submit" value="Filter">

                    </form>
                    </div>
                    <div class="clear"></div>

                    <div class="advert-analysis-overview-mwrap">

                        <div class="advert-analysis-bl3">
                        <div class="advert-analysis-overview-wrap bl3">
                        <div class="advert-analysis-overview-head"><?php if(!current_user_can('publish_adverts')){ _e('Charges', 'ADVERT_TEXTDOMAIN'); }else{ _e('Earnings', 'ADVERT_TEXTDOMAIN'); }?></div>
                        <div class="advert-analysis-overview-body"><?php if(!empty($queryitems[0]->total)){echo number_format_i18n($queryitems[0]->total, 2);}else{echo '0';} ?></div>
                        </div>

                        <div class="advert-analysis-overview-wrap bl3">
                        <div class="advert-analysis-overview-head">Impressions</div>
                        <div class="advert-analysis-overview-body"><?php if(!empty($queryitems[0]->imp)){echo number_format_i18n($queryitems[0]->imp);}else{echo '0';} ?></div>
                        </div>

                        <div class="advert-analysis-overview-wrap bl3">
                        <div class="advert-analysis-overview-head">Clicks</div>
                        <div class="advert-analysis-overview-body"><?php if(!empty($queryitems[0]->click)){echo number_format_i18n($queryitems[0]->click);}else{echo '0';} ?></div>
                        </div>
                        </div>

                        <?php if(current_user_can('publish_adverts')){ ?>

                        <div class="advert-analysis-bl2">
                        <div class="advert-analysis-overview-wrap bl2">
                        <div class="advert-analysis-overview-head"><?php _e('Active Advertisers', 'ADVERT_TEXTDOMAIN');?></div>
                        <div class="advert-analysis-overview-body">
                        <?php
                        $args = array('post_type' => 'advert-advertiser', 'post_status' => 'publish');
                        $posts = new WP_Query($args);
                        wp_reset_postdata();
                        echo number_format_i18n($posts->found_posts);
                        ?>
                        </div>
                        </div>

                        <div class="advert-analysis-overview-wrap bl2">
                        <?php
                        $url = esc_url(admin_url( 'edit.php?post_status=pending&post_type=advert-advertiser' ));
                        $args = array('post_type' => 'advert-advertiser', 'post_status' => 'pending');
                        $posts = new WP_Query($args);
                        wp_reset_postdata();
                        if($posts->found_posts > 0){echo '<div class="advert-analysis-overview-head"><a href="'.$url.'">'. __('Pending Advertisers', 'ADVERT_TEXTDOMAIN') .'</a></div>';}
                        else{echo '<div class="advert-analysis-overview-head">'. __('Pending Advertisers', 'ADVERT_TEXTDOMAIN') .'</div>';}
                        ?>
                        <div class="advert-analysis-overview-body">
                        <?php
                        echo number_format_i18n($posts->found_posts);
                        ?>
                        </div>
                        </div>
                        </div>

                        <?php } ?>

                        <div class="advert-analysis-bl2">
                        <div class="advert-analysis-overview-wrap bl2">
                        <div class="advert-analysis-overview-head"><?php _e('Active Campaigns', 'ADVERT_TEXTDOMAIN');?></div>
                        <div class="advert-analysis-overview-body">
                        <?php
                        $args = array('post_type' => 'advert-campaign', 'post_status' => 'publish');
                        $posts = new WP_Query($args);
                        echo number_format_i18n($posts->found_posts);
                        wp_reset_postdata();
                        ?>
                        </div>
                        </div>

                        <div class="advert-analysis-overview-wrap bl2">
                        <?php
                        $url = esc_url(admin_url( 'edit.php?post_status=pending&post_type=advert-campaign' ));
                        $args = array('post_type' => 'campaign', 'post_status' => 'pending');
                        $posts = new WP_Query($args);
                        wp_reset_postdata();
                        if($posts->found_posts > 0){echo '<div class="advert-analysis-overview-head"><a href="'.$url.'">'. __('Pending Campaigns', 'ADVERT_TEXTDOMAIN') .'</a></div>';}
                        else{echo '<div class="advert-analysis-overview-head">'. __('Pending Campaigns', 'ADVERT_TEXTDOMAIN') .'</div>';}
                        ?>
                        <div class="advert-analysis-overview-body">
                        <?php
                        echo number_format_i18n($posts->found_posts);
                        ?>
                        </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php

    }




}// End AdVert Analysis Class