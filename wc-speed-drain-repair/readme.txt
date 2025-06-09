=== WooCommerce Speed Repair ===
Contributors: wpfixit  
Donate link: https://wpfixit.com  
Tags: woocommerce, performance, speed, optimization, scripts, dequeue  
Requires at least: 5.6  
Tested up to: 6.8  
Requires PHP: 7.2  
Stable tag: 4.0  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Make WooCommerce sites BLAZING fast by disabling unused scripts and styles with one-click toggles.

== Description ==

WooCommerce is powerful — but it loads dozens of scripts and styles even when they’re not needed, which can slow your site down.

This plugin gives you an **instant performance boost** by letting you disable unnecessary WooCommerce frontend assets on non-WooCommerce pages.

= Features =
- One-click toggles for each WooCommerce asset (JS/CSS)
- Grouped by functionality: Core Scripts, Cart, Block Styles, General Styles
- AJAX-saving (no page reloads)
- Toast confirmation on setting save
- "Select All" and "Deselect All" buttons to quickly apply optimizations

By reducing what loads on non-commerce pages, your site becomes leaner and faster — especially for blog, landing, or informational pages.

= How It Works =

Only WooCommerce pages (like shop, cart, checkout, and product pages) truly need WooCommerce assets. So this plugin:

- Lets you turn off WooCommerce assets on non-WooCommerce pages
- Automatically preserves functionality where needed (e.g. checkout/cart)
- Saves bandwidth, server load, and improves core Web Vitals

There’s no need to write any code or modify theme files.

== Installation ==

= Install via WP Admin =

1. Go to **Plugins > Add New**
2. Click **Upload Plugin** and select the `.zip` file
3. Click **Install Now**, then **Activate**

= Install via FTP =

1. Upload the extracted plugin folder to `/wp-content/plugins/`
2. Activate it from your **Plugins** page

== Frequently Asked Questions ==

= Does it work with all WooCommerce themes? =

Yes! It works with any theme that uses standard WooCommerce scripts/styles.

= Do I need to configure anything after activating? =

Yes — visit **Settings > Woo Speed Repair** to turn off assets you don’t need.

= Is it safe to disable everything? =

Some assets are safe to disable if you’re not using that WooCommerce feature sitewide. For example: if you don’t use the mini cart, you can safely disable its styles/scripts on non-Woo pages.

= Can I undo a setting? =

Yes, just toggle the switch again to re-enable the asset.

== Screenshots ==

1. Performance gains shown in PageSpeed test
2. Admin UI with toggle switches
3. Script asset debugger in the admin bar
4. CSS asset debugger in the admin bar


== Changelog ==

= 4.0 =
* Released: June 9, 2025
* Complete redesign with toggle-based UI
* AJAX saving and toast confirmations
* Added "Select All" / "Deselect All" controls
* Cleaned up legacy codebase
* Improved WooCommerce 6.8 compatibility

= 2.5 =
* Released: June 1, 2025
* Adjusted to work with WordPress 6.8 release

= 2.2 =
* Released: November 14, 2023
* Adjusted to work with WordPress 6.4 release

= 2.0 =
* Released: January 19, 2022
* Prepared plugin for WordPress 6.0

= 1.3 =
* Released: January 21, 2020
* Updated for newer WP core

= 1.2 =
* Released: November 21, 2017
* Maintenance update

= 1.0 =
* Released: September 30, 2015
* First release


== Upgrade Notice ==

= 4.0 =  
Major upgrade: new UI, toggle logic, AJAX settings, better compatibility

= 2.0 =  
Ready for WordPress 6.0

= 1.3 =  
Asset logic changes and compatibility