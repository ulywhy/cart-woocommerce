# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.1.0] - 2019-11-14

### Added
- [PLUG-473] CHANGELOG.md added in repository.
- [PLUG-456] Feature currency conversion returned.
- [PLUG-467] New feature to check if cURL is installed

### Changed
 - [PLUG-459] 
   - Fixed credential issue when the plugin is upgraded from version 3.x.x to 4xx. Unable to save empty credential.
   - Fixed issue to validate credential when checkout is active. The same problem occurs when removing the enabled checkout credential.
   - Fixed error: Undefined index: MLA in WC_WooMercadoPago_Credentials.php on line 163.
   - Fixed error: Call to a member function analytics_save_settings() in WC_WooMercadoPago_Hook_Abstract.php on line 68. Has affected users that cleared the credential and filled new credential production.
   - Fixed load of WC_WooMercadoPago_Module.php file.
   - Fixed error Uncaught Error: Call to a member function homologValidate().
   - Fixed error Undefined index: section in WC_WooMercadoPago_PaymentAbstract.php on line 303. Affected users who did not have homologous accounts
   - Fixed issue to validate credential when checkout is active. The same problem occurs when removing the enabled checkout credential.
   - Fixed issue to calculate commission and discount.
   - Fixed issue on methadata.
   - Fixed Layout of checkout custom input.
   - Fixed translation of Modo Producción, Habilitá and definí
- [PLUG-459-2] Refactored Javascript code for custom checkout Debit and credit card. Performance improvement, reduced number of SDK calls. Fixed validation errors. Javascript code refactored to the order review page. Removed select from mexico payment method.
- [PLUG-462] 
  - Fixed Uncaught Error call to a member function update_status() in WC_WooMercadoPago_Notification_Abstract.php. Handle Mercado Pago Notification Failures and Exceptions.
  - Fixed Uncaught Error call to a member function update_status() in WC_WooMercadoPago_Notification_Abstract.php. Handle Mercado Pago Notification Failures and Exceptions.
- [PLUG-463] 
  - Remove Mercado Creditos from Custom CHO OFF. 
  - Fix PT-BR debit card translation on admin.
  - Fix PT-BR debit card translation on checkout.
  - Remove "One Step Checkout" from CHO Custom Off.
- [PLUG-466] Removed feature and support to Mercado Envios shipping. Before install the plugin verify if your store has another method of shipping configured.
- [PLUG-470] Fixed issue to check if WooCommerce plugin is installed
- [PLUG-455] Curl Validation.
- [PLUG-474] Removed mercadoenvios/WC_WooMercadoPago_Product_Recurrent.php file.








