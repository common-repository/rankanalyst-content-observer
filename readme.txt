=== RankAnalyst Content Observer ===
Contributors: sbroschart
Donate link: https://rankanalyst.com/
Tags: seo, search engine optimization, content, observation, quality management, modification, update, monitor, customer journey, user experience, ux, usability, readability
Requires at least: 2.7
Tested up to: 5.1
Stable tag: 1.1
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

To monitor content modifications for optimal quality management. Provides Rest API for RankAnalyst Lab.

== Description ==
Modifications to a site's content can have a significant impact on your site's rankings in search results. Therefore not only search engine optimizers, but also content administrators should be informed about all modifications to assure an optimal user experience.

With 'RankAnayst Content Observer' you can track all content modifications that an editor has made to articles or pages within a WordPress installation. The plugin page lists the name of the post, the time and the scope of the modification. The column 'Mutation' shows to which extent (percentage) existing text has been edited. If the text has been shortened or extended, this can be seen in the column 'extension'. If important elements for search engines are also affected by the modifications, the plugin indicates this in the 'notice' column. The modifications made can be further examined and tracked by clicking on the 'examine' button. The WordPress versioning system opens for this purpose.

RankAnalyst Content Observer' provides a Rest API that can be used by the performance analysis system 'RankAnalyst Lab'. The Rest-API informs about concrete modifications for a given period of time and makes them available for an in-depth analysis via rankanalyst.de or rankanalyst.com.

Settings: If you would like to be informed immediately about a content modification by e-mail, enter one or more (comma-separated) e-mail addresses in the corresponding input box of the 'Alert' section. If you leave this field blank, this functionality is deactivated.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use 'RankAnalyst Content Observer' screen to configure the alert functionality and to show recent content changes.

== Frequently Asked Questions ==

= Plugin-Homepage =

The homepage of the plugin can be found at https://rankanalyst.com/content-observer-wp-plugin

= Support =

We respond to questions in our forum at https://rankanalyst.com/forum/

== Screenshots ==

1. pluginpage.png

== Changelog ==

= 1.1 =
* New Feature: Search functionality
* New feature: Recreate listing from database
* Overhault Admin Page

= 1.0 =
* REST API Extensions
* Debugging

= 0.91 =
* RankAnalyst Lab apikey integration

= 0.9 =
* Scope of changes, comparison option, Rest API for RankAnalyst Lab, email notification
