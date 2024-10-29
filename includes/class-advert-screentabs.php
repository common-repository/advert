<?php

class AdVert_Screentabs {

    public function __construct() {

        add_action('current_screen', array($this, 'advert_load_screen_tabs'));

    }

    public function advert_load_screen_tabs(){

        $currentScreen = get_current_screen();
        $screenID      = preg_replace('/-[0-9]+/', '', $currentScreen->id);

        if ($screenID == 'toplevel_page_advert'){

        //hack cant get the screen options to work any other way
        //layout_columns
        add_screen_option( 'advert-dashboard' );

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#dashboard">AdVert Documentation</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'dashboard_help_tab',
           'title'	=> __('Overview', 'ADVERT_TEXTDOMAIN'),
           'content'	=> '<p>' . __( 'The Dashboard shows the latest AdVert news, provides an option for suggestions or feedback, displays the latest updates and lets you subscribe to AdVert. A welcome panel is also included for people new to AdVert, which helps walk you through the process of creating a new advertisement from start to finish.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }

        if ($screenID == 'toplevel_page_advert-user'){

        //hack cant get the screen options to work any other way
        add_screen_option( 'advert-user-dashboard' );

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#user-dashboard">AdVert Documentation</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'dashboard_user_help_tab',
           'title'	=> __('Overview', 'ADVERT_TEXTDOMAIN'),
           'content'	=> '<p>' . __( 'The Dashboard allows you to add AdCredits, view recent transaction history and update your advertising information. A welcome panel is also included if your are new to AdVert, which helps walk you through the process of creating a new advertisement from start to finish.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }

        if ($currentScreen->post_type == 'advert-banner'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#banners">Documentation on Banners</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        //show to AdVert admins only
        if ( current_user_can( 'publish_adverts' ) ){
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'banner_help_tab1',
           'title'	 => __('Overview', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'The Banner is the actual advertisement being displayed to the end user (image, video or text). Depending on your settings in the AdVert Control Panel, the end user can be a visitor, member or both.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'banner_help_tab2',
           'title'	 => __('Getting Started', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'If you have no Banners, click the Add New button near the top of the page, below this Help area. From there, complete the form and click save Draft, Publish or Update.<br /><br />You will need to have at least one published Advertiser and one published Location to complete the Banner setup. Once the Banner is published, you will then need to publish a campaign with the desired Banner.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        }
        //show to advertisers if applicable
        else{
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'banner_help_tab1',
           'title'	 => __('Overview', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'The Banner is the actual advertisement being displayed to the end user (image, video or text). Depending on settings, the end user can be a visitor, member or both.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'banner_help_tab2',
           'title'	 => __('Getting Started', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'If you have no Banners, click the Add New button near the top of the page, below this Help area.<br /><br />From there, complete the form and click save Draft, Submit for Review or Update. Once the Banner is published, you will then need a campaign with the desired Banner.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        }

        }


        if ($currentScreen->post_type == 'advert-advertiser'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#advertisers">Documentation on Advertisers</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'advertiser_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'Advertisers are the main component that links the Banner and Campaign together.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'advertiser_help_tab2',
           'title'	 => __('Getting Started', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'If you have no Advertisers, click the Add New button near the top of the page, below this Help area.<br /><br />From there, complete the form, then Publish the Advertiser. Once the Advertiser is published, you will need to verify you have Locations available and then create an Banner and Campaign.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($currentScreen->post_type == 'advert-campaign'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#campaigns">Documentation on Campaigns</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'campaign_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'The Campaign essentially pieces everything together, taking the information from the Advertiser and Banners and presenting it into the queue of what will be displayed on the website. This queue can change depending on the Banner priority, Campaign priority and Location flow. The Campaign is part of the Advertisement Process Flow.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'campaign_help_tab2',
           'title'	 => __('Getting Started', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'If you have no Campaigns, click the Add New button near the top of the page, below this Help area.<br /><br />There needs to be at least one Banner available to complete the Campaign form.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($currentScreen->post_type == 'advert-location'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#locations">Documentation on Locations</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'location_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'The Location is the primary starter for the Advertisement Process Flow, allowing Locations to be assigned a shortcode to perform all the necessary tasks to display a Campaign. There are several options for Locations, which determine the type and flow of Campaigns and there attached Banners.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );
        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'location_help_tab2',
           'title'	 => __('Getting Started', 'ADVERT_TEXTDOMAIN'),
           'content' => '<p>' . __( 'If you have no Locations, click the Add New button near the top of the page, below this Help area. There needs to be at least one Location published to create new Banners and Campaigns (which display the Advertisements).', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }



        if ($screenID == 'advert_page_advert-cp-general'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#control-panel-settings">Documentation on General Settings</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'general_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'These are the settings that control how your advertisements will be displayed, how users will interact with AdVert and many other options. Before doing anything else, review these settings and make necessary changes if needed.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($screenID == 'advert_page_advert-cp-users'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#control-panel-settings">Documentation on User Settings</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'user_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'These are the settings that control how your advertisements will be displayed, how users will interact with AdVert and many other options. Before doing anything else, review these settings and make necessary changes if needed.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($screenID == 'advert_page_advert-cp-ads'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#control-panel-settings">Documentation on Ad Settings</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'ads_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'These are the settings that control how your advertisements will be displayed, how users will interact with AdVert and many other options. Before doing anything else, review these settings and make necessary changes if needed.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($screenID == 'advert_page_advert-analysis-overview'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#analysis-and-drilldown">Documentation on Analysis Overview</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'analysis_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'This is a brief overview of how your advertisements are performing, number of Advertisers and Campaigns. You can specify a range of performance using the filter.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($screenID == 'advert_page_advert-analysis-drilldown'){

        //hack cant get the screen options to work any other way
        add_screen_option( 'advert-analysis-drilldown' );

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#analysis-and-drilldown">Documentation on Analysis Drilldown</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'drilldown_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'This is an in-depth analysis of your advertisements, with the ability to drilldown to specifics. A date range can be set within the query to limit the results.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }


        if ($screenID == 'advert_page_advert-transactions'){

        $currentScreen->set_help_sidebar(
        __('<p>For more information:</p><p><a href="https://norths.co/advert/documentation/#transactions">Documentation on Transactions</a></p><p><a href="https://norths.co/advert/support/">AdVert Support Forum</a></p>', 'ADVERT_TEXTDOMAIN')
        );

        $currentScreen->add_help_tab( 
        array(
           'id'	=> 'transaction_help_tab',
           'title'	=> __('Overview'),
           'content'	=> '<p>' . __( 'A detailed look at the transaction history, with several groupings to identify the date, reason and amount of AdCredits removed or added.', 'ADVERT_TEXTDOMAIN' ) . '</p>',
        )
        );

        }

    }//end screentabs


}// End AdVert Screentabs Class
