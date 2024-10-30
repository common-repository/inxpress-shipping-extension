=== InXpress-Shipping-Extension ===
Contributors: InXpress
Donate link:
Tags: woocommerce
Requires at least: wordpress 4.4 or higher and woocommerce 3.5.2 or higher
Tested up to: 6.5
Stable tag: 3.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a new shipping method provided by InXpress.

== Description ==

This woocommerce shipping extension provides a new shipping method by DHL and calculates the shipping rates based on the product weight or dimensional weight (the one which is heavier).

== Installation ==

You can either install it automatically from the WordPress plugin directory, or do it manually:

1.
Step 1 - Go to admin panel (wp-admin).
Step 2 - Go to Plugins --> Add New, then find the Search plugins box and enter InXpress.  It should find the InXpress-Shipping-Extension.
Step 3 - Click "Install Now"
Step 4 - Click "Activate"

OR

2.
Step 1. Unzip the archive and put the `InXpress_Shipping` folder into your plugins folder (/wp-content/plugins/).
Step 2. Activate the plugin from the Plugins menu.

= Usage =

1. In the Admin Panel go to "WooCommerce --> Settings" Then in the top tabs bar, click on the "Shipping", you will see the shipping extension "InXpress" under this top tabs bar.
   Click on "InXpress", when you will click on this link the extension configuration page will open, now do the settings according to your details.

2. Go to Products --> Manage Dimensional Weight to add dimensional weights by importing CSV file with product id, length, width and height as columns.

3. Go to Products --> Manage DHL Boxes to add, edit or delete DHL boxes available for shipping.

4. Go to edit any product and then click "Manage Dimensional weight" link at sidebar for adding or updating dimensional weight for thar particular product.

== Frequently Asked Questions ==

= What is Dim weight? =

Dim stands for - Dimensional weight which includes product's length, width & height

== Screenshots ==

1. InXpress Settings
2. Manage DHL Box
3. Manage Dimensional Weight
4. Manage Dimensional weight in edit product section.
5. Frontend cart page showing this shipping rate.

== Changelog ==

3.5.2 Enabled FedEx Carrier for Australian Stores
3.5.1 Fixed a bug affecting activation
3.5.0 Added FedEx and StarTrack carriers for Australia, new activation process
3.4.3 Adding Loomis Carrier for Canadian Stores
3.4.2 Fixing deprecation in Canpar method
3.4.1 Set the default Gateway to US if not defined in settings
3.4.0 Added increased timeout when fetching rates, WP 6.0 testing
3.3.1 Fixed handling fees now entered in dollars instead of cents
3.3.0 Adds a new service for Canadian customers
3.2.3 Initial version of the shipping extension

== Upgrade Notice ==

3.4.3 Loomis rating only available in Canada
