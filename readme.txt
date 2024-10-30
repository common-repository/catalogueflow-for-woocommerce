=== CatalogueFlow for WooCommerce ===
Contributors: claudiom
Tags: product description, ai, catalogueflow, generar descripciones de producto, crear descripciones.
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.1.4
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CatalogueFlow plugin for WooCommerce to generate product descriptions using AI.

== Description ==

This plugin integrates WooCommerce with the CatalogueFlow platform to generate product descriptions using artificial intelligence. By using this plugin, your product data will be sent to the CatalogueFlow service to process and generate descriptions.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-catalogueflow` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->CatalogueFlow screen to configure the plugin.
4. Enter your CatalogueFlow App Key and App Secret. If you don't have an account, register [here](https://www.catalogflow.ai/woocommerce/).
5. Configure your webhook in CatalogueFlow settings [here](https://app.catalogueflow.com/settings/webhooks) using the URL provided in the plugin settings.

== Usage ==

1. Go to any WooCommerce product edit page.
2. Use the "Generate Description" button to create a product description using AI.
3. The description will be automatically updated in the product description field.

== Frequently Asked Questions ==

= What is CatalogueFlow? =

CatalogueFlow is a platform that uses artificial intelligence to generate high-quality product descriptions.

= How do I get an App Key and App Secret? =

Register on the CatalogueFlow platform [here](https://www.catalogflow.ai/es/woocommerce.html) to obtain your App Key and App Secret.

= How do I configure the webhook? =

Go to your CatalogueFlow account settings [here](https://app.catalogueflow.com/settings/webhooks) and add the webhook URL provided in the plugin settings.

== Screenshots ==

1. Plugin settings page
2. Product edit page with Generate Description button

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.0.3 =
* The personal shopper feature now as a wordpress plugin.

== Upgrade Notice ==

= 1.0.1 =
Change functions name to match with the Guidelines.

= 1.0.2 =
Update URL and readme

= 1.0.3 =
* The personal shopper feature now as a wordpress plugin.

= 1.1.4 =
* The Dinamic FAQ for your products

== Video Tutorial ==

[Watch the video tutorial](https://youtu.be/MvFqW7G4uXg)

== Privacy and Data Policy ==

The plugin sends product data to CatalogueFlow’s API to generate descriptions. No personal data is sent, only product data like SKU, title, and attributes. For more details, please review CatalogueFlow's [Terms of Service](https://www.catalogflow.ai/terms-and-conditions/) and [Privacy Policy](https://www.catalogflow.ai/terms-and-conditions/).