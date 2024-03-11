=== Carbon Footprint ===

Contributors:      littlebigthing
Requires at least: 5.8
Tested up to:      6.2
Requires PHP:      5.6
Stable tag:        1.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Tags:              sustainability, sustainable, site health, carbon footprint

== Description ==

The Carbon Footprint plugin is a collection of tests and information that provide insight about the digital sustainability of your website. You get an estimate of the carbon footprint and information about the (type of) energy use of your site.

The plugin uses the [Website Carbon API](https://api.websitecarbon.com), kindly provided by [Wholegrain Digital](https://www.wholegraindigital.com).
You can test your site directly (including other pages then the homepage) using the [Website Carbon Calculator](https://www.websitecarbon.com).

Currently, the plugin gives you the following information:

* Tests and information to [WordPress’ Site Health tool](https://wordpress.org/documentation/article/site-health-screen/).
* The tests determine 
    1. whether your site is running on renewable energy
    2. how your homepage’s carbon footprint is situated relative to other websites measured by the Website Carbon Calculator
* The information gives you further insight in 
    1. the type of energy used by your server
    2. the size of your homepage in KB (kilobytes)
    3. the amount of CO2 (in g (grams)) emitted each time one visits your homepage
* Links to learn more about improving your website’s sustainability
* A dashboard widget for admininstrators to warn them when the homepage’s footprint is worse then 90% of the tested sites

*Note that the results are cached for a week.*

== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **Carbon Footprint**.
3. Install and activate the Performance Lab plugin.

= Manual installation =

1. Upload the entire `carbon-footprint` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Sustainable WP plugin.

= After activation =

1. Visit the new **Tools > Site Health** menu.
2. Find out whether the tests succeded or failed concerning the sustainabilty of your site
3. Find out more information about your site via Site Health Info under the tab Sustainability

== Frequently Asked Questions ==

= What is the purpose of this plugin? =

This plugin seeks to spread awareness about the fact that digital has a physical footprint. 
It might be small, but your website contributes to this as well. 
Making you website more energy efficient will decrease its carbon footprint, but it will give it performance improvement as well.

= Can I use this plugin on my production site? =

Yes, no harm is expected. But you do it at your own risk.

== Changelog ==

** Version 1.2.1 **

* modify plugin description to clarify what plugin does

** Version 1.2 **

* add carbon rating

** Version 1.1.1 **

* fix cron

Initial release

**Features**

* Tests and information to [WordPress’ Site Health tool](https://wordpress.org/documentation/article/site-health-screen/).
* The tests determine 
    1. whether your site is running on renewable energy
    2. how your homepage’s carbon footprint is situated relative to other websites measured by the Website Carbon Calculator
* The information gives you further insight in 
    1. the type of energy used by your server
    2. the size of your homepage in KB (kilobytes)
    3. the amount of CO2 (in g (grams)) emitted each time one visits your homepage
* Links to learn more about improving your website’s sustainability.
* A dashboard widget for admininstrators to warn them when the homepage’s footprint is worse then 90% of the tested sites
