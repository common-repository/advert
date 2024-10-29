<?php
/**
 * Plugin Name: AdVert
 * Plugin URI: https://norths.co/advert/
 * Description:  An advertising placement service for WordPress, designed for publishers that want to display text, video or image advertisements.
 * Version: 1.0.5
 * Author: Jeremy North
 * Author URI: https://norths.co/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Text Domain: ADVERT_TEXTDOMAIN
 */

/* Copyright 2015  Jeremy North  (email : jeremy.north@norths.co)

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as 
   published by the Free Software Foundation.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


                              ____             
                             /    \           ___
                            /  /\  \         /  /    
                           /  /  \  \___    /  /
                          /  /    \___  \  /  /
                         /__/         \  \/  /
                                       \____/

                    | Welcome to AdVert for WordPress |

*/



 /**
 * Define ADVERT_PLUGIN_URL constant for global use
 */
if (!defined('ADVERT_PLUGIN_URL'))
    define('ADVERT_PLUGIN_URL', plugin_dir_url(__FILE__));


if (!defined('ADVERT_PLUGIN_DIR'))
    define('ADVERT_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Define basename constant for global use
 */
if (!defined('ADVERT_PLUGIN_FILE'))
    define('ADVERT_PLUGIN_FILE', basename(__FILE__));

/**
 * Define full path
 */
if (!defined('ADVERT_PLUGIN_FULL_PATH'))
    define('ADVERT_PLUGIN_FULL_PATH', __FILE__);

/**
 * Hooks to install, deactivate and uninstall
 */
require( ADVERT_PLUGIN_DIR . 'includes/advert-setup.php');

register_activation_hook(__FILE__, 'advert_activate');
register_deactivation_hook(__FILE__, 'advert_deactivate');
register_uninstall_hook( __FILE__, 'advert_uninstall' );


/**
 * The core class that loads everything
 */
require plugin_dir_path(__FILE__) . 'includes/class-advert.php';


//add quick link on the plugin page
add_filter('plugin_action_links', 'advert_plugin_action_links', 10, 2);

function advert_plugin_action_links($links, $file) {
static $advert_plugin;
    if (!$advert_plugin) {
        $advert_plugin = plugin_basename(__FILE__);
    }
    if ($file == $advert_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=advert-cp-general">Control Panel</a>';
        array_unshift($links, $settings_link);
    }
return $links;
}


/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_advert_for_wordpress() {

    $plugin = new AdVert_For_Wordpress();

}

run_advert_for_wordpress();