=== AdVert ===
Contributors: norths
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=NPK8ETF9PAKPN
Tags: advert, ads, advertising, analytics, banners, impressions, multisite, notifications, options, reports, tracking, transactions
Requires at least: 4.2, PHP 5.4
Tested up to: 4.3.1
Stable tag: 1.0.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
Take back control of your website and advertise your way, without restrictions or having to buy feature enhancements - Advertising made simple!
 
== Description ==
 
AdVert is an advertising placement service for WordPress and designed for publishers that want to display text, video or image advertisements. In addition, you can also insert code for ad and affiliate networks, assign an AdVert manager to manage your day-to-day operations and allow subscribers to become advertisers. There are tons of options to control your advertising process, analysis to view performance and integration with PayPal so your advertisers can buy AdCredits to start advertising.
 
**Important links**
 
* [Docs](https://norths.co/advert/documentation/) - Having trouble, checkout the documentation section
* [Support Forum](https://norths.co/advert/support/) - AdVert also has a dedicated support forum
 
**Features other plugins charge for that AdVert can do for free**

* Multisite install
* Cross site advertising
* Weight
* Schedule
* Limit Impressions
* Prevent Spammed Clicks
* Analysis (all users)
* Export Analysis (csv)
* Track clicks on iframes
* Backend dashboard (all users)
* Dashboard notifications
* Email notifications
* Have actual advertisers create ads
* Adblock detection
* Encrypted ad links
* Ad feedback
* Work with cache plugins
 
**Even more features**

* Click and Impression tracking
* Detailed and robust analysis tool
* Display a custom message for empty Ad Locations
* Group Advertisements into Campaigns
* Virtually an unlimited number of advertisements can be displayed, created and managed
* Use Widgets and Shortcodes to display ads
* Lock rates
* Designed to work with almost any theme
* Designed to work with multiple currencies
* Registered advertisers can purchase AdCredits automatically
* Registered advertisers can create their own advertisement and campaigns
* Designed to for desktop, mobile and tablets
* Includes a default payment method
* Assign AdVert managers to control your advertisements
* Standard Locations preinstalled
* Let Advertisers view analysis (no more worrying about sending emails about performance)
* Payment hooks for developers to create their own payment methods
* Display a custom message to visitors blocking ads
* Disable Ad tracking for logged in users
* Self-hosted ads (removes the middle man – image, video or text)
* View transaction history
* Onscreen help tabs
* Translation ready
* Schedule campaigns to start or stop
* Multiple pricing models
* Bot filtering
 
== Installation ==
 
How to install the plugin and get it working?
 
= Using The WordPress Dashboard =
 
1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'AdVert'
3. Click 'Install Now'
4. Activate AdVert on the Plugin dashboard
 
= Uploading in WordPress Dashboard =
 
1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `advert.zip` from your computer
4. Click 'Install Now'
5. Activate AdVert in the Plugin dashboard
 
= Using FTP =
 
1. Download 'advert.zip'
2. Extract the 'advert' directory to your computer
3. Upload the 'advert' directory to the `/wp-content/plugins/` directory
4. Activate AdVert in the Plugin dashboard
 
== Frequently Asked Questions ==
 
= Why no pro version for AdVert = 
 
To make advertising on WordPress easier
 
== Screenshots ==

1. Welcome Screen
2. Analysis
3. Analysis Drilldown
4. Advertisers
5. Banners
6. Campaigns
7. Locations
8. AdCredits with PayPal
 
== Changelog ==

= 1.0.5 =
* Updated bot filtering

= 1.0.4 =
* Added AID, BID, CID and LID - Advertiser, Banner, Campaign and Location ID's to the list table for easier access and information
* Fixed an error that occurred when trying to save a campaign when AdCredits were disabled
* Drilldown Analysis updated from default 30 days to today, which helps reduce DB queries
* Minor code tweaks
* Updated language file
* Updated Banner and screenshots

= 1.0.3 =
* Standard payment method PayPal updated - IPN listener fixed (needed to automatically add AdCredits), return and cancel return links updated - paypal live tested
* On some sites, Ad clicks would not redirect - Hardcoded the URL for the redirect
* Updated verbiage in Control Panel for Emptying the AdVert DB - only empties the clicks and impressions table, payments are not affected
* Minor bug fixes and coding tweaks
* Updated language file
* Added banner and screenshots

= 1.0.2 =
* Updated links to norths.co/advert/
* Security fix for campaign pricing models
* Minor code tweaks

= 1.0.1 =
* Updated a few links that still had a hash tag
* Updated language file
* Minor bug fixes

= 1.0 = 
* AdVert initial launch
 
== Upgrade Notice ==
 
= 1.0.0 =
AdVert initial launch