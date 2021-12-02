=== Lara's Google Analytics ===
Tags: analytics,google analytics,google analytics dashboard,google analytics plugin,google analytics widget
Contributors: amribrahim, laragoogleanalytics
Requires PHP: 5.6.0
Requires at least: 4.7.0
Tested up to: 5.8
Stable tag: 3.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Full width Google Analytics dashboard widget for Wordpress admin interface, which also inserts latest Google Analytics tracking code to your pages.

== Description ==

<strong>Lara's Google Analytics.</strong>

Adds a full width Google Analytics dashboard widget for WordPress admin interface, and inserts latest Google Analytics tracking code to all your pages.

> Check a fully working demo, including all <strong>Google Analytics by Lara</strong> free and premium features at : [Google Analytics Dashboard Widget](https://www.xtraorbit.com/wordpress-google-analytics-dashboard-widget/).

== Installation ==
1. Download the plugin zip file.
1. Using WordPress dashboard, click on **Plugins** then, **Add New**.
1. Click on **Upload Plugin**, then click **Browse**.
1. Select the plugin zip file from your computer and click **Open**.
1. When done, Activate the plugin through the Plugins menu in WordPress.

== Screenshots ==
1. Setup Screen, showing Express and Advanced Google Analytics setup options.
2. Full width Google Analytics sessions tab.
3. Google Analytics Sessions tab on wide screens.
4. Google Analytics - Pro Version Only - Check metrics for any date range, not just the last 30 days.
5. Google Analytics - Pro Version Only - Countries with most visitors to your website, with beautiful map.
6. Google Analytics - Browsers used to view your website, along with their versions.
7. Google Analytics - Visitors Operating Systems, along with their versions.
8. Google Analytics - Pro Version Only - Keywords, provided by Google Search Console.
9. Google Analytics - Pages visited on your website.
10. Google Analytics - Visitors languages.
11. Google Analytics - Screen Resolutions used to view your website.
12. Google Analytics - Pro Version Only - Traffic Sources, showing who is sending you visitors.

== Changelog ==
= 3.3.3 =
* 18-Jan-2021
* Fix : Several small tweaks.
* Fix : [pro] Show correct number of active users per page, for realtime tab.

= 3.3.2 =
* 29-Aug-2020
* Fix : Add domain names to links.
* New : Detect Ad Blockers.

= 3.3.1 =
* 21-Aug-2020
* Fix : WooCommerce graph was returning the last 10 orders only.

= 3.3.0 =
* 26-Jul-2020
* New : Code refactoring.
* New : The plugin no more depends on sessions, to allow comparability with sessionless hosts.
* Update : Updates to various Javascript libraries and  Font Awesome 5.
* Fix : Various minor bug fixes and performance improvements. 

= 3.2.2 =
* 27-Apr-2020
* Fix : Show the correct dates in negative UTC timezone.
* Update : The plugin now requires PHP version 5.6 or higher, and WordPress 4.7 or higher.
* Rearranging the changelog.

= 3.2.1 =
* 13-Mar-2020
* Fix : Site Health errors, caused by session_start.

= 3.2.0 =
* 5-Mar-2020
* New : [pro] Added the ability to lock settings for users.
* New : WooCommerce earnings graph.
* Fix : Compatibility issues with other plugins, that causes the widget to display a blank page, instead of the main graph.

= 3.1.0 =
* 26-Jan-2020
* New : Localization support.
* New : Review notice.

= 3.0.0 =
* 02-Jan-2020
* New : Code refactoring.
* New : Various security checks and validation for POST variables.
* New : Implementing Multisite Multi-Network in the Pro Version.
* New : Implementing Permissions Control in the Pro Version.

= 2.0.7 =
* 14-Oct-2019
* Fix : restrict settings to super admins.

= 2.0.6 =
* 14-Oct-2019
* Fix : Extra validation for POST variables.
* Fix : Pages tab shows correct percentage values.

= 2.0.5 =
* 14-Oct-2019
* Fix : Validate POST variables.

= 2.0.4 =
* 8-Dec-2018
* update : moment.js updated to v2.23.0.

= 2.0.3 =
* 8-Dec-2018
* update : WordPress v 5.0 compatibility.

= 2.0.2 =
* 8-May-2018
* New : Adding IP anonymization.

= 2.0.1 =
* 17-Feb-2018
* Fix : Support URL and plugin overview URL updated.

= 2.0.0 =
* 12-Dec-2017
* New : Migrate from analytics.js to Global Site Tag (gtag.js).
* New : Implementing Real Time tracking in the Pro Version.
* New : Implementing Devices Tab.
* New : Moving Pages, Browsers, Languages, Operating Systems, and Screen Resolutions tabs to the free version. 
* New : Warn when using different time zones.

= 1.1.2 =
* 8-June-2017
* Fix : WordPress v4.8 compatibility.

= 1.1.1 =
* 8-Jan-2017
* Fix : Changed cURL query handling, to prevent requests to google from returning errors.

= 1.1 =
* 30-Oct-2016
* Fix : Combining Javascript and CSS files into 2 files.
* Fix : Checking for PHP version upon plugin activation.
* Fix : Several minor improvements.
* New : Code refactoring.

= 1.0.6 =
* 29-Aug-2016
* Fix : Check for cURL extension.
* New : Use PageViews in the pages tab.

= 1.0.5 =
* 7-May-2016
* Fix : Track subscribers and anonymous visitors only.

= 1.0.4 =
* 27-April-2016
* Fix : Only users with 'manage_options' can access the widget.

= 1.0.3 =
* 26-April-2016
* Revised description.

= 1.0.2 =
* 22-April-2016
* Initial release.