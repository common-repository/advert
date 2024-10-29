<?php
/**
 * Where the logging occurs - AdVert Log
 *
 * Goes through a number of checks to determine the correct location, then the campaign, and lastly, the actual banner to display.
 * Once the campaign is determined, there are more checks to determine if the advertiser has enough funds, if the campaign has enough funds.
 * 
 *
 * @since 1.0.0
 */
class AdVert_Log{
    
	/**
	 * Holds the lists of recent device and also os.
	 *
	 * @var array
	 * @access protected
	 */
	protected $device_array = array(
			            '/windows nt 10.0/i'    =>  'Windows 10',
			            '/windows nt 6.3/i'     =>  'Windows 8.1',
			            '/windows nt 6.2/i'     =>  'Windows 8',
                        '/windows nt 6.1/i'     =>  'Windows 7',
                        '/windows nt 6.0/i'     =>  'Windows Vista',
                        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                        '/windows nt 5.1/i'     =>  'Windows XP',
                        '/windows xp/i'         =>  'Windows XP',
                        '/windows nt 5.0/i'     =>  'Windows 2000',
                        '/macintosh|mac os x/i' =>  'Mac OS X',
                        '/mac_powerpc/i'        =>  'Mac OS 9',
                        '/linux/i'              =>  'Linux',
                        '/ubuntu/i'             =>  'Ubuntu',
                        '/iphone/i'             =>  'iPhone',
                        '/ipod/i'               =>  'iPod',
                        '/ipad/i'               =>  'iPad',
                        '/android/i'            =>  'Android',
                        '/blackberry/i'         =>  'BlackBerry',
                        '/webos/i'              =>  'Mobile'
	);

	/**
	 * Holds the lists of popular browsers.
	 *
	 * @var array
	 * @access protected
	 */
	protected $browser_array = array(
			            '/edge/i'       =>  'Microsoft Edge',
			            '/msie/i'       =>  'Internet Explorer',
                        '/firefox/i'    =>  'Firefox',
                        '/chrome/i'     =>  'Chrome',
                        '/opera/i'      =>  'Opera',
			            '/safari/i'     =>  'Safari',
                        '/netscape/i'   =>  'Netscape',
                        '/maxthon/i'    =>  'Maxthon',
                        '/konqueror/i'  =>  'Konqueror',
                        '/mobile/i'     =>  'Mobile Browser'
	);

	protected $device;

	protected $browser;

	/**
	 * Log table name without prefix
	 *
	 * Also use when creating log table.
	 *
	 * @var string
	 * @access public
	 */

	public static $log_table_name = 'advert_logged';
	
	/**
	 * Log Table Name 
	 *
	 * Dynamically change when table prefix has changed.
	 *
	 * @var string
	 * @access protected
	 */

	protected $log_table;

	protected $ads_data;
	
	/**
	 * Log Type 
	 *
	 * Can be 'i', 'c' or 'f'
	 *
	 * 'I' stands for impression
	 *
	 * 'L' stands for loaded impression
	 *
	 * 'C' stands for click
	 *
     * 'F' stands for feedback
     *
	 * @var string
	 * @access protected
	 */

	protected $type;

	/**
	 * Constructor of log
	 *
	 * @param string $data The data from either advert_ads_click or advert_ads_view
	 */

	function __construct($data,$type='i'){
	global $wpdb;
	$this->log_table =  $wpdb->prefix . self::$log_table_name;
	$this->check_browser();
	if($this->check_browser()){
	    if($this->manage_data($data,$type))
		    $this->add_log();
	    }
	}
	
	function manage_data($data,$type){
 
    global $advert_options;
    $advert_log_ads = 0;
    $advert_members = 0;
    if( array_key_exists('advert_log_ads',$advert_options) ){
    $advert_log_ads = intval($advert_options['advert_log_ads']);
    }
    if( array_key_exists('advert_disable_member_ads',$advert_options) ){
    $advert_members = intval($advert_options['advert_disable_member_ads']);
    }

    //check if logging ads is enabled
    if( $advert_log_ads === 1 ){return false;}

    //check if user is logged in and disable member ads logging is true
    if( is_user_logged_in() && $advert_members === 1 ){return false;}  
    
	$this->type = $type;
	$data = explode('-|-',$data);
	if(count($data) < 5 || count($data) > 6)
	return false;
	$this->ads_data['banner_id'] = intval($data[0]); 
	$this->ads_data['camp_id'] = intval($data[1]); 
	$this->ads_data['adv_id'] = intval($data[2]); 
	$this->ads_data['location_id'] = intval($data[3]); 
	$this->ads_data['location_url'] = $data[4];
    if(count($data) === 6){
    $this->ads_data['banner_feedback'] = $data[5]; 
	}
    else{
    $this->ads_data['banner_feedback'] = 0;    
    }
    return true;
	}
	
	function check_browser(){

	foreach($this->device_array as $pattern => $each){
		if(preg_match($pattern , $_SERVER['HTTP_USER_AGENT'])){
		$os = $each;
		break;
		}
	}
	foreach($this->browser_array as $pattern => $each){
		if(preg_match($pattern , $_SERVER['HTTP_USER_AGENT'])){
		$browser = $each;
		break;
		}
	}

	$this->device = $os;
	$this->browser = $browser;

    if(empty($os) && empty($browser))
        return false;
	if(empty($os))
		$this->device = 'Unknown Device';
	if(empty($browser))
		$this->browser = 'Unknown Browser';

    return true;
	}

	function add_log(){

	global $wpdb;
	if(ADVERT_Shortcode::is_available($this->ads_data['camp_id'],$this->ads_data['location_id'])){
        if($this->ads_data['banner_feedback'] === 0){

        $valid = 0;

        try{
            $log_cookie = (isset($_COOKIE['avviewed']) ? stripslashes(decryptLink(htmlspecialchars($_COOKIE['avviewed']), 'campimp')) : NULL);
            if( is_serialized($log_cookie) ){    
                $log   = unserialize($log_cookie);
                $valid = intval(advert_is_utf8($log_cookie));
            }
        }
        catch(Exception $e){
            unset($_COOKIE['avviewed']);
            $log = NULL;
        }

        if($valid != 1){
            unset($_COOKIE['avviewed']);
            $log = NULL;
        }

		$log[$this->ads_data['camp_id']] = isset($log[$this->ads_data['camp_id']]) ? $log[$this->ads_data['camp_id']]+1 : 1;
		setcookie('avviewed', encryptLink(serialize($log), 'campimp') , current_time('timestamp') + (86400 * 30), '/');
		$price = $this->get_price();
		$count_type = $this->type == 'i' ? '_total_view' : '_total_click';
                
                if ( $price > 0 ){
                //$company_credits = get_post_meta($this->ads_data['adv_id'], 'company_credits' , true);
                $campaign_charges = get_post_meta($this->ads_data['camp_id'], 'campaign_charges' , true);
                //$charged_adv = ($company_credits - $price);
                $charged_camp = ($campaign_charges + $price);
                //update_post_meta($this->ads_data['adv_id'], 'company_credits', $charged_adv );
                update_post_meta($this->ads_data['camp_id'], 'campaign_charges', $charged_camp );
	            }
        }
        else{
        $price = 0;    
        }

        //check the type: image, video or text
        $banner_type = 'unknown';
        if( has_post_thumbnail($this->ads_data['banner_id']) && get_post_meta($this->ads_data['banner_id'], 'banner_video_url', true) == '' ){
           $banner_type = 'image';
        }
        elseif( get_post_meta($this->ads_data['banner_id'], 'banner_video_url', true) != '' ){
           $banner_type = 'video';
        }
        elseif( get_post_meta($this->ads_data['banner_id'], 'banner_text_ad1', true) !='' || get_post_meta($this->ads_data['banner_id'], 'banner_text_ad2', true) !='' ){
           $banner_type = 'text';
        }

		$id = $wpdb->insert($this->log_table, 
		array( 
			'ip_address'     => $_SERVER['REMOTE_ADDR'], 
			'browser'        => $this->browser,
			'device'         => $this->device,
			'typeof'         => $this->type,
			'location_id'    => $this->ads_data['location_id'],
			'camp_id'        => $this->ads_data['camp_id'],
			'banner_id'     => $this->ads_data['banner_id'],
			'adv_id'         => $this->ads_data['adv_id'],
			'price'          => number_format($price, 2),
			'time'           => current_time('mysql'),
			'url'            => $this->ads_data['location_url'],
            'feedback'       => $this->ads_data['banner_feedback'],
            'location_name'  => get_the_title( $this->ads_data['location_id'] ),
            'adv_name'       => get_the_title( $this->ads_data['adv_id'] ),
            'camp_name'      => get_the_title( $this->ads_data['camp_id'] ),
            'camp_type'      => get_post_meta($this->ads_data['camp_id'], 'campaign_price_model', true),
            'banner_name'   => get_the_title( $this->ads_data['banner_id'] ),
            'banner_type'   => $banner_type
		),
		array( 
			'%s', 
			'%s',
			'%s', 
			'%s',
			'%d', 
			'%d',
			'%d', 
			'%d',
			'%f',
			'%s', 
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'

		) );
	   
            if( $price > 0 ){
                $transaction = apply_filters('remove_adcredits', $this->ads_data['adv_id'], number_format($price, 2), '', $this->type);

            if($transaction)
            do_action('calc_adcredits', $this->ads_data['adv_id']);
            
            }     

		}
	}

	
	/**
	 * Log query
	 *
	 *
	 * @param string $type Can be 'camp_id', 'location_id', 'adv_id' or 'banner_id'
	 *
	 * @param int $id The ID of $type
	 *
	 * @param string $log_type 'i' or 'c'
	 *
	 * @param datetime $time_con Add condition since this value
	 *
	 */

	static function get_log_by($type, $id, $log_type='i', $time_con = ''){
	global $wpdb;
	$log_table = $wpdb->prefix . self::$log_table_name;
	if($time_con)
	$time_con = " AND time >= '$time_con' ";
	return $wpdb->get_row("SELECT COUNT(id) as num , SUM(price) as payment FROM $log_table WHERE $type = $id AND typeof = '{$log_type}' $time_con");
	}

	/**
	 * Multiple Clicks
	 *
	 * checks the IP Address of the clicker
	 *
	 * @param string $type will be the IP ADDRESS
         *
	 */

	static function get_click_by($type, $id, $time_con = '', $fbvalue = ''){
	global $wpdb;
	$log_table = $wpdb->prefix . self::$log_table_name;
    $dude = $_SERVER['REMOTE_ADDR'];
	if($time_con)
	$time_con = " AND time > NOW() - INTERVAL $time_con ";
    if(!empty($fbvalue))
    $fbvalue = " AND feedback = '$fbvalue' ";
	return $wpdb->get_var("SELECT COUNT(id) FROM $log_table WHERE banner_id = $id AND ip_address = '{$dude}' AND typeof = '{$type}' $fbvalue $time_con ");
	}

	/**
	 * Get price for location
	 *
	 * Special Price is used for more important campaign or banner
	 *
	 */

	function get_price(){
    $custom = get_post_meta($this->ads_data['banner_id'], 'banner_custom_html', true);
	$rotation = get_post_meta($this->ads_data['location_id'],'location_rotation',true);
	$model = get_post_meta($this->ads_data['camp_id'],'campaign_price_model',true);
	$log = self::get_log_by('camp_id',$this->ads_data['camp_id']);
    $bypass = intval(get_post_meta($this->ads_data['adv_id'], 'advert_bypass_adcredits' , true));
    global $advert_options;
    $advert_clicked = 0;
    if( array_key_exists('advert_spammed_clicks',$advert_options) ){
    $advert_clicked = intval($advert_options['advert_spammed_clicks']);
    }
    if( $advert_clicked === 1 ){
    $cond = '12 HOUR';
    $clicked = self::get_click_by('c',$this->ads_data['banner_id'],$cond);

    //limits the amount of clicks per person in a timespan
    if ($clicked > 1){
       return 0;
    }
    }

    //if advertiser has disabled adcredits
    if ($bypass === 1){
       return 0;
    }

    //if this click is a feedback click
    if ($this->ads_data['banner_feedback'] > 0){
       return 0;
    }

    //if click or impression is a custom banner
    if ($custom !=''){
       return 0;
    }

    //campaign models
    if(($model == 'cpp')){
        return 0;
    }

    global $advert_options;
    $advert_locked_rates = 0;
    if( array_key_exists('advert_lock_rates',$advert_options) ){
    $advert_locked_rates = intval($advert_options['advert_lock_rates']);
    }

    //campaign models
    $external_link = get_post_meta($this->ads_data['banner_id'], 'banner_link', true);
    //Check if link exists, if not charge based on cpm
	if(($model == 'cpm' && $this->type == 'i' && ($log->num%1000) == 999) || (empty($external_link) && ($log->num%1000) == 999 && $model != 'cpp')){
       $lockedin_rate_array = get_post_meta($this->ads_data['camp_id'], 'campaign_locked_rates', false);
       if(is_array($lockedin_rate_array)){
           $lockedin_rate = $lockedin_rate_array[$this->ads_data['location_id']];
       }
       if( $advert_locked_rates === 0 && !empty($lockedin_rate)){
	   return $lockedin_rate;
       }
       else{
       $location_price = get_post_meta($this->ads_data['location_id'],'location_price',true);
       return $location_price;  
       }
	}

    //campaign models
	if(($model == 'cpc' && $this->type == 'c')){
       $lockedin_rate_array = unserialize(get_post_meta($this->ads_data['camp_id'], 'campaign_locked_rates', true));
       if(is_array($lockedin_rate_array)){
           $lockedin_rate = $lockedin_rate_array[$this->ads_data['location_id']];
       }
       if( $advert_locked_rates === 0 && !empty($lockedin_rate) ){
	   return $lockedin_rate;
       }
       else{
       $location_price = get_post_meta($this->ads_data['location_id'],'location_price',true);
       return $location_price;          
       }

	}

	return 0;

	}

}// End Advert Log Class