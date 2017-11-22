=== WooCommerce MercadoPago ===
Contributors: mercadopago, mercadolivre, claudiosanches
Donate link: https://www.mercadopago.com.br/developers/
Tags: ecommerce, mercadopago, woocommerce
Requires at least: 4.8
Tested up to: 4.8
Requires PHP: 5.6
Stable tag: 3.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Offer to your clients the best experience in e-Commerce by using Mercado Pago as your payment method.

== Description ==

This module enables WooCommerce to use Mercado Pago as a payment method for purchases in your e-commerce store. By offering a nice set of tools like LatAm support, several card acquires, tickets, discounts, subscriptions, and many others e-Commerce features, this plugin wants to bring the best experience in payment checkouts.

= Why chose Mercado Pago =
Mercado Pago owns the highest security standards with PCI certification level 1 and a specialized internal team working on fraud analysis. With Mercado Pago, you will be able to accept payments from the most common brands of credit card, offer purchase installments options and receive your payment with antecipation. You can also enable your customers to pay in the web or in their mobile devices.

= Mercado Pago Main Features =
* Online and real-time processment through IPN/Webhook mechanism;
* High approval rate with a robust fraud analysis;
* Potential new customers with a base of more than 120 millions of users in Latin America;
* PCI Level 1 Certification;
* Support to major credit card brands;
* Payment installments;
* Anticipation of receivables in D+2 or D+14 (According to Mercado Pago terms and conditions);
* Payment in one click with Mercado Pago basic and custom checkouts;
* Payment via tickets;
* Subscriptions;
* Seller's Protection Program.

= Compatibility =

- WooCommerce 3.0 or later.

== Installation ==

You have two ways to install this module: from your WordPress Store, or by downloading and manually copying the module directory.

= Install from WordPress =
1. On your store administration, go to **Plugins** option in sidebar;
2. Click in **Add New** button and type "Woo Mercado Pago Module" in the **Search Plugins** text field. Press Enter;
3. You should find the module read to be installed. Click install.

= Manual Download =
1. Get the module sources from a repository (<a href="https://github.com/mercadopago/cart-woocommerce/archive/master.zip">Github</a> or <a href="https://downloads.wordpress.org/plugin/woocommerce-mercadopago.3.0.5.zip">WordPress Plugin Directory</a>);
2. Unzip the folder and find "woocommerce-mercadopago" directory;
3. Copy "woocommerce-mercadopago" directory to **[WordPressRootDirectory]/wp-content/plugins/** directory.

To confirm that your module is really installed, you can click in **Plugins** item in the store administration menu, and check your just installed module. Just click **enable** to activate it and you should receive the message "Plugin enabled." as a notice in your WordPress.

= Configuration =
1. On your store administration, go to **WooCommerce > Settings > Checkout** tab. In **Checkout Options**, you can find configurations for **Mercado Pago - Basic Checkout**, **Mercado Pago - Custom Checkout**, and **Mercado Pago - Ticket**.
	* To get your **Client_id** and **Client_secret** for your country, you can go to: <a href="https://www.mercadopago.com/mla/account/credentials?type=basic">Argentina</a>, <a href="https://www.mercadopago.com/mlb/account/credentials?type=basic">Brazil</a>, <a href="https://www.mercadopago.com/mlc/account/credentials?type=basic">Chile</a>, <a href="https://www.mercadopago.com/mco/account/credentials?type=basic">Colombia</a>, <a href="https://www.mercadopago.com/mlm/account/credentials?type=basic">Mexico</a>, <a href="https://www.mercadopago.com/mpe/account/credentials?type=basic">Peru</a>, <a href="https://www.mercadopago.com/mlu/account/credentials?type=basic">Uruguay</a>, and <a href="https://www.mercadopago.com/mlv/account/credentials?type=basic">Venezuela</a>.
	* And to get your **Public Key**/**Access Token** you can go to: <a href="https://www.mercadopago.com/mla/account/credentials?type=custom">Argentina</a>, <a href="https://www.mercadopago.com/mlb/account/credentials?type=custom">Brazil</a>, <a href="https://www.mercadopago.com/mlc/account/credentials?type=custom">Chile</a>, <a href="https://www.mercadopago.com/mco/account/credentials?type=custom">Colombia</a>, <a href="https://www.mercadopago.com/mlm/account/credentials?type=custom">Mexico</a>, <a href="https://www.mercadopago.com/mpe/account/credentials?type=custom">Peru</a>, <a href="https://www.mercadopago.com/mlu/account/credentials?type=custom">Uruguay</a>, and <a href="https://www.mercadopago.com/mlv/account/credentials?type=custom">Venezuela</a>.
2. For the solutions **Mercado Pago - Basic Checkout**, **Mercado Pago - Custom Checkout**, and **Mercado Pago - Ticket**, you can:
	* Enable/Disable your plugin, so you can allow specific solutions for your business;
	* Set up your credentials (Client_id/Client_secret for Basic Checkout and Subscriptions, Public Key/Access Token for Custom Checkout and Ticket);
	* Check your IPN URL, where you will get notified about payment updates;
	* Set the title of the payment option that will be shown to your customers;
	* Set the description of the payment option that will be shown to your customers;
	* Set the description that will be shown in your customer's invoice (for Custom Checkout and Ticket);
	* Set binary mode that when charging a credit card, only [approved] or [reject] status will be taken (for Custom Checkout);
	* Set the category of your store;
	* Set stock reduction behavior (for Ticket);
	* Set a prefix to identify your store, when you have multiple stores for only one Mercado Pago account;
	* Define how your customers will interact with Mercado Pago to pay their orders (Basic Checkout and Subscriptions);
	* Define discounts by payment method;
	* Configure the after-pay return behavior (Basic Checkout);
	* Configure the maximum installments allowed for your customers (for Basic Checkout);
	* Configure the payment acquirers that you want to not work with Mercado Pago (for Basic Checkout);
	* Configure call-back URLs for after-pay behavior (for Basic Checkout and Subscriptions);
	* Enable coupon of campaigns for discounts (for Custom Checkout and Ticket);
	* Enable currency conversion;
	* Enable/disable sandbox mode, where you can test your payments in Mercado Pago sandbox environment (for Basic Checkout and Custom Checkout);
	* Enables/disable system logs.

= In this video, we show how you can install and configure from your WordPress store =

[youtube https://www.youtube.com/watch?v=CgV9aVlx5SE]

== Frequently Asked Questions ==

= What is Mercado Pago? =
Please, take a look: https://vimeo.com/125253122

= Any questions? =
Please, check our FAQ at: https://www.mercadopago.com.br/ajuda/

== Screenshots ==

1. `Custom Checkout`

2. `One Click Payment`

3. `Tickets & Discounts`

4. `Plugin Options`

== Changelog ==

= v3.0.5 (22/11/2017) =
* Bug fixes
	- Fixed a bug in the URL of javascript source for light-box window.

= v3.0.4 (13/11/2017) =
* Improvements
	- Improved webhook of ticket printing to a less generic one.
* Bug fixes
	- FIxed a bug related to payment status of tickets.

= v3.0.3 (25/10/2017) =
* Features
	- Rollout to Uruguay for Custom Checkout and Tickets.
* Bug fixes
	- Not showing ticket form when not needed.

= v3.0.2 (19/10/2017) =
* Bug fixes
	- Fixed the absence of [zip_code] field in registered tickets for Brazil.

= v3.0.1 (04/10/2017) =
* Bug fixes
	- We fixed a Javascript problem that are occurring when payments were retried in custom checkout and tickets;
	- Resolved the size of Mercado Pago icon in checkout form.
* Improvements
	- Allowing absence of SSL if debug mode is enabled;
	- Optmizations in form layout of custom checkout and tickets;
	- Validating currency consistency before trying conversions;
	- References to the new docummentations.

= v3.0.0 - 25/09/2017 =
* Features
	- All features already present in <a href="https://br.wordpress.org/plugins/woocommerce-mercadopago/">Woo-Mercado-Pago-Module 2.x</a>;
	- Customization of status mappings between order and payments.
* Improvements
	- Added CNPJ document for brazilian tickets;
	- Optimization in HTTP requests and algorithms;
	- Removal of several redundancies;
	- HTML and Javascript separation;
	- Improvements in the checklist of system status;
	- More intuitive menus and admin navigations.

= 2.0.9 - 2017/03/21 =

* Included sponsor_id to indicate the platform to MercadoPago.

= 2.0.8 - 2016/10/24 =

* Open MercadoPago Modal when the page load.
* Changed notification_url to avoid payment notification issues.

= 2.0.7 - 2016/10/21 =

* Improve MercadoPago Modal z-index to avoid issues with any theme.

= 2.0.6 - 2016/07/29 =

* Fixed fatal error on IPN handler while log is disabled.

= 2.0.5 - 2016/07/04 =

* Improved Payment Notification handler.
* Added full support for Chile in the settings.

= 2.0.4 - 2016/06/22 =

* Fixed `back_urls` parameter.

= 2.0.3 - 2016/06/21 =

* Added support for `notification_url`.

= 2.0.2 - 2016/06/21 =

* Fixed support for WooCommerce 2.6.

= 2.0.1 - 2015/03/12 =

* Removed the SSL verification for the new MercadoPago standards.

= 2.0.0 - 2014/08/16 =

* Adicionado suporte para a moeda `COP`, lembrando que depende da configuração do seu MercadoPago para isso funcionar.
* Adicionado suporte para traduções no Transifex.
* Corrigido o nome do arquivo principal.
* Corrigida as strings de tradução.
* Corrigido o link de cancelamento.

== Upgrade Notice ==

= 2.0.8 =

* Open MercadoPago Modal when the page load.
* Changed notification_url to avoid payment notification issues.
