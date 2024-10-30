=== Komper ===
Contributors: vandai
Donate link: http://www.vnetware.com/
Tags: compare,comparison,product,price,chart,table
Requires at least: 3.0.1
Tested up to: 3.5
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to create side-by-side product comparison table.

== Description ==

Komper is a plugin to create side-by-side product comparison. With this plugin, you can create product specifications, add products information, and display the comparison form widget to the front page.
In front page, user may search the product to compare via comparison form widget with autocomplete feature.
The result will be displayed in table chart.

*Komper Features*:

*   Create product specifications fields dynamically
*   Drag and drop fields to change the order of the fields
*   Widget supported
*   Search product with autocomplete features
*   Display side-by-side product comparison in table chart


**Coming Soon**

*   More Field type
*   New table layout
*   Customizeable css table


Please note this is the free version.<br />
In this free version, you can create up to 15 fields and display 2 products to compare.

__Upgrade to Pro version__, and get more features:

*   Unlimited product specification fields
*   Unlimited product to compare
*   Shortcode supported, Insert product comparison table and comparison form in any posts or pages.
*   Full Support

Get Pro version here:
[http://www.vnetware.com/](http://www.vnetware.com/)

Try Demo:
[http://demo.vnetware.com/komper](http://demo.vnetware.com/komper/)


*Note*<br />
You can try public release version here:
http://src.vnetware.com/komper/pub/

This release include quick fixes, new features, and other workaround.


== Installation ==

1. Upload `komper.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to *(Settings - Komper Settings)*
4. Activate Komper Widget in *(Appearance - Widgets)*
5. Update your Permalink. Go to *(Settings - Permalink)* and click *"Save Changes"*. This is required for Output page as we use rewrite rules to display Comparison results. You don't have to change the permalink, just click "save".

== Frequently Asked Questions ==

= How to install it? =

Go to Installation section please!

= Then how to use it? =

You must create fields first. Go to *(Settings - Komper Settings)*, and on tab Field List add some fields. 
Fields is your product's spesification. You can create as many as you like. But you don't have to create field for *Product Name* and *Product Images*, as it is aready predefined.

After creating some fields, you can now add Products. Go to *Products List* tab. You can see that the form is based on your Field list.

= How to change fields order? =

Just drag and drop it!

= Does it support widget? =

Of course! You can add comparison form widget from *Appearance - Widgets*

= What is 'Open in New Window' on widget form? =

When user enter products to compare, the results will be opened in new window (or new tab).

= And how about 'Without Themes Header'? =

When you checked that, the result page will be displayed in single page **without** your wordpress header (and footer). This is good when your Wordpress themes doesn't have enough space to displaying comparison table.
If you do not checked it, the result will be displayed with header and footer, but without sidebar.

= Now how to compare products? =

Via widget in front-page. Enter product name, autocomplete will appear, then select product. Click Compare button, the result will be displayed in side-by-side table.

= Comparison page can not display, 404 Error! =

Try to change the wordpress permalink to *"Post Name"*


If you still have any question or need assistance, please post on *Support* tab.


== Screenshots ==

1. Create product spesification fields
2. Product list
3. Add product spesification, the field forms based on product specification fields.
4. Comparison Form Widget with autocomplete
5. Add product comparison table into post / page (Pro version)
6. Comparison table chart and form in posts (Pro version)
7. Sample output comparison table chart without wordpress header
8. Sample output comparison table chart __with__ wordpress header

== Changelog ==

= 1.1.4 =
* FIX: Error on upload product images
* FIX: Failed on displaying product images

= 1.1.3 =
* Support new Wordpress 3.5
* FIX: Fatal Error for function: SanitizeValue()
* FIX minor bugs
* NEW: Field Group
* UPDATE: Field limit now up-to 15!
* UPDATE: Image thumbnail now reduce to 180x180 pixels

= 1.1.2 =
* FIX: Failed on creating field-values table on installation
* FIX: Empty result on displaying output

= 1.1.1 =
* NEW: Add field formatting
* NEW: Insert comparison table chart into any posts and pages

= 1.1 =
* Beta version
* NEW: Support widget
* NEW: Drag and drop
* NEW: Autocomplete

= 1.0 =
* Alpha version
* For testing purpose

== Upgrade Notice ==

N/A