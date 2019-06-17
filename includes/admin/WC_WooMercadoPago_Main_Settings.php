<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class WC_WooMercadoPago_Main_Settings
 */
class WC_WooMercadoPago_Main_Settings
{

    /**
     * WC_WooMercadoPago_Main_Settings constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', function () {
            add_options_page(
                'Mercado Pago Options', 'Mercado Pago', 'manage_options', 'mercado-pago-settings',
                function () {

                    // Verify permissions.
                    if (!current_user_can('manage_options')) {
                        wp_die(__('You do not have sufficient permissions to access this page.'));
                    }

                    // Check for submits.
                    if (isset($_POST['submit'])) {
                        update_option('_mp_public_key', isset($_POST['public_key']) ? $_POST['public_key'] : '', true);
                        update_option('_mp_access_token', isset($_POST['access_token']) ? $_POST['access_token'] : '', true);
                        update_option('_mp_success_url', isset($_POST['success_url']) ? $_POST['success_url'] : '', true);
                        update_option('_mp_fail_url', isset($_POST['fail_url']) ? $_POST['fail_url'] : '', true);
                        update_option('_mp_pending_url', isset($_POST['pending_url']) ? $_POST['pending_url'] : '', true);
                        WC_WooMercadoPago_Module::is_valid_sponsor_id(isset($_POST['sponsor_id']) ? $_POST['sponsor_id'] : '');
                        update_option('_mp_order_status_pending_map', isset($_POST['order_status_pending_map']) ? $_POST['order_status_pending_map'] : '', true);
                        update_option('_mp_order_status_approved_map', isset($_POST['order_status_approved_map']) ? $_POST['order_status_approved_map'] : '', true);
                        update_option('_mp_order_status_inprocess_map', isset($_POST['order_status_inprocess_map']) ? $_POST['order_status_inprocess_map'] : '', true);
                        update_option('_mp_order_status_inmediation_map', isset($_POST['order_status_inmediation_map']) ? $_POST['order_status_inmediation_map'] : '', true);
                        update_option('_mp_order_status_rejected_map', isset($_POST['order_status_rejected_map']) ? $_POST['order_status_rejected_map'] : '', true);
                        update_option('_mp_order_status_cancelled_map', isset($_POST['order_status_cancelled_map']) ? $_POST['order_status_cancelled_map'] : '', true);
                        update_option('_mp_order_status_refunded_map', isset($_POST['order_status_refunded_map']) ? $_POST['order_status_refunded_map'] : '', true);
                        update_option('_mp_order_status_chargedback_map', isset($_POST['order_status_chargedback_map']) ? $_POST['order_status_chargedback_map'] : '', true);
                        update_option('_mp_statement_descriptor', isset($_POST['statement_descriptor']) ? $_POST['statement_descriptor'] : '', true);
                        if (isset($_POST['category_id'])) {
                            update_option('_mp_category_id', $_POST['category_id'], true);
                            $categories_data = WC_WooMercadoPago_Module::$categories;
                            update_option('_mp_category_name', $categories_data['store_categories_id'][$_POST['category_id']], true);
                        } else {
                            update_option('_mp_category_id', '', true);
                            update_option('_mp_category_name', 'others', true);
                        }
                        update_option('_mp_store_identificator', isset($_POST['store_identificator']) ? $_POST['store_identificator'] : '', true);
                        update_option('_mp_custom_banner', isset($_POST['custom_banner']) ? $_POST['custom_banner'] : '', true);
                        update_option('_mp_custom_domain', isset($_POST['custom_domain']) ? $_POST['custom_domain'] : '', true);
                        update_option('_mp_currency_conversion_v0', isset($_POST['currency_conversion_v0']) ? $_POST['currency_conversion_v0'] : '', true);
                        update_option('_mp_currency_conversion_v1', isset($_POST['currency_conversion_v1']) ? $_POST['currency_conversion_v1'] : '', true);
                        update_option('_mp_debug_mode', isset($_POST['debug_mode']) ? $_POST['debug_mode'] : '', true);
                        update_option('_mp_sandbox_mode', isset($_POST['sandbox_mode']) ? $_POST['sandbox_mode'] : '', true);
                    }

                    // Mercado Pago logo.
                    $mp_logo = '<img width="185" height="48" src="' . plugins_url('../../assets/images/mplogo.png', __FILE__) . '">';
                    // Check WooCommerce.
                    $has_woocommerce_message = class_exists('WC_Payment_Gateway') ?
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                        __('WooCommerce is installed and enabled.', 'woocommerce-mercadopago') :
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/error.png', __FILE__) . '"> ' .
                        __('You don\'t have WooCommerce installed and enabled.', 'woocommerce-mercadopago');
                    // Creating PHP version message.

                    // Check for PHP version and throw notice.
                    $min_php_message = '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                        __('Your PHP version is OK.', 'woocommerce-mercadopago');

                    if (version_compare(PHP_VERSION, WC_WooMercadoPago_Module::MIN_PHP, '<=')) {
                        $min_php_message = '<img width="14" height="14" src="' . plugins_url('../../assets/images/warning.png', __FILE__) . '"> ' .
                            sprintf(__('Your PHP version do not support this module. You have %s, minimal required is %s.', 'woocommerce-mercadopago'),phpversion(), WC_WooMercadoPago_Module::MIN_PHP);
                    }

                    // Check cURL.
                    $curl_message = in_array('curl', get_loaded_extensions()) ?
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                        __('cURL is installed.', 'woocommerce-mercadopago') :
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/error.png', __FILE__) . '"> ' .
                        __('cURL is not installed.', 'woocommerce-mercadopago');
                    // Check SSL.
                    $is_ssl_message = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ?
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/warning.png', __FILE__) . '"> ' .
                        __('SSL is missing in your site.', 'woocommerce-mercadopago') :
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                        __('Your site has SSL enabled.', 'woocommerce-mercadopago');
                    // Check porduct dimensions.
                    global $wpdb;
                    $all_product_data = $wpdb->get_results(
                        'SELECT ID FROM `' . $wpdb->prefix . 'posts` where post_type="product" and post_status = "publish"'
                    );
                    $is_all_products_with_valid_dimensions = WC_WooMercadoPago_Module::is_product_dimensions_valid($all_product_data) ?
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                        __('Your products have theirs dimensions well defined.', 'woocommerce-mercadopago') :
                        '<img width="14" height="14" src="' . plugins_url('../../assets/images/warning.png', __FILE__) . '"> ' .
                        __('You have product(s) with invalid dimensions.', 'woocommerce-mercadopago');
                    // Create links for internal redirections to each payment solution.
                    $gateway_buttons = '<strong>' .
                        '<a class="button button-primary" href="' . esc_url(admin_url(
                            'admin.php?page=wc-settings&tab=checkout&section=woo-mercado-pago-basic')) .
                        '">' . __('Basic Checkout', 'woocommerce-mercadopago') . '</a>' . ' ' .
                        '<a class="button button-primary" href="' . esc_url(admin_url(
                            'admin.php?page=wc-settings&tab=checkout&section=woo-mercado-pago-custom')) .
                        '">' . __('Custom Checkout', 'woocommerce-mercadopago') . '</a>' . ' ' .
                        '<a class="button button-primary" href="' . esc_url(admin_url(
                            'admin.php?page=wc-settings&tab=checkout&section=woo-mercado-pago-ticket')) .
                        '">' . __('Ticket', 'woocommerce-mercadopago') . '</a>';
                    
                    if (get_option('_site_id_v1', '') == 'MCO') {
                        $gateway_buttons .= ' <a class="button button-primary" href="' . esc_url(admin_url(
                                'admin.php?page=wc-settings&tab=checkout&section=woo-mercado-pago-pse')) .
                            '">' . __('PSE', 'woocommerce-mercadopago') . '</a>';
                    }
                    $gateway_buttons .= '</strong>';
                    // Statement descriptor.
                    $statement_descriptor = get_option('_mp_statement_descriptor', 'Mercado Pago');
                    // Get categories.
                    $store_categories_id = WC_WooMercadoPago_Module::$categories['store_categories_id'];
                    $category_id = get_option('_mp_category_id', 0);
                    if (count($store_categories_id) == 0) {
                        $store_category_message = '<img width="14" height="14" src="' . plugins_url('../../assets/images/warning.png', __FILE__) . '">' . ' ' .
                            __('Configure your Public_key and Access_token to have access to more options.', 'woocommerce-mercadopago');
                    } else {
                        $store_category_message = __('Define which type of products your store sells.', 'woocommerce-mercadopago');
                    }
                    // Store identification.
                    $store_identificator = get_option('_mp_store_identificator', 'WC-');
                    // Custom domain for IPN.
                    $custom_banner = get_option('_mp_custom_banner', '');
                    // Custom domain for IPN.
                    $custom_domain = get_option('_mp_custom_domain', '');
                    if (!empty($custom_domain) && filter_var($custom_domain, FILTER_VALIDATE_URL) === FALSE) {
                        $custom_domain_message = '<img width="14" height="14" src="' . plugins_url('../../assets/images/warning.png', __FILE__) . '"> ' .
                            __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
                    } else {
                        $custom_domain_message = sprintf('%s',
                            __('If you want to use a custom URL for IPN inform it here.<br>Format should be as: <code>https://yourdomain.com/yoursubdomain</code>.', 'woocommerce-mercadopago')
                        );
                    }
                    // Debug mode.
                    $_mp_debug_mode = get_option('_mp_debug_mode', '');
                    if (empty($_mp_debug_mode)) {
                        $is_debug_mode = '';
                    } else {
                        $is_debug_mode = 'checked="checked"';
                    }
                    // Sandbox mode.
                    $_mp_sandbox_mode = get_option('_mp_sandbox_mode', '');
                    if (empty($_mp_sandbox_mode)) {
                        $is_sandbox_mode = '';
                    } else {
                        $is_sandbox_mode = 'checked="checked"';
                    }

                    // ===== v1 verifications =====
                    // Trigger v1 API to validate credentials.
                    $site_id_v1 = '';
                    if (WC_WooMercadoPago_Credentials::validate_credentials_v1()) {
                        $site_id_v1 = get_option('_site_id_v1', '');
                        $v1_credentials_message = WC_WooMercadoPago_Credentials::validate_credentials_v1() ?
                            '<img width="14" height="14" src="' . plugins_url('../../assets/images/check.png', __FILE__) . '"> ' .
                            __('Your <strong>public_key</strong> and <strong>access_token</strong> are <strong>valid</strong> for', 'woocommerce-mercadopago') . ': ' .
                            '<img style="margin-top:2px;" width="18.6" height="12" src="' .
                            plugins_url('../../assets/images/' . $site_id_v1 . '/' . $site_id_v1 . '.png', __FILE__) . '"> ' .
                            WC_WooMercadoPago_Module::get_country_name($site_id_v1) :
                            '<img width="14" height="14" src="' . plugins_url('../../assets/images/error.png', __FILE__) . '"> ' .
                            __('Your <strong>public_key</strong> and <strong>access_token</strong> are <strong>not valid</strong>!', 'woocommerce-mercadopago');
                    } else {
                        $v1_credentials_message = '<img width="14" height="14" src="' . plugins_url('../../assets/images/error.png', __FILE__) . '"> ' .
                            __('Your <strong>public_key</strong> and <strong>access_token</strong> are <strong>not valid</strong>!', 'woocommerce-mercadopago');
                    }

                    $v1_credential_locales = sprintf(
                        '%s <a href="https://www.mercadopago.com/mla/account/credentials?type=custom" target="_blank">%s</a>, ' .
                        '<a href="https://www.mercadopago.com/mlb/account/credentials?type=custom" target="_blank">%s</a>, ' .
                        '<a href="https://www.mercadopago.com/mlc/account/credentials?type=custom" target="_blank">%s</a>, ' .
                        '<a href="https://www.mercadopago.com/mco/account/credentials?type=custom" target="_blank">%s</a>, ' .
                        '<a href="https://www.mercadopago.com/mlm/account/credentials?type=custom" target="_blank">%s</a>, ' .
                        '<a href="https://www.mercadopago.com/mpe/account/credentials?type=custom" target="_blank">%s</a> %s ' .
                        '<a href="https://www.mercadopago.com/mlv/account/credentials?type=custom" target="_blank">%s</a>',
                        __('These credentials are used in <strong>Basic Checkout</strong>, <strong>Custom Checkout</strong>, <strong>Tickets</strong> and <strong>Subscriptions</strong>. Access it for your country:<br>', 'woocommerce-mercadopago'),
                        __('Argentina', 'woocommerce-mercadopago'),
                        __('Brazil', 'woocommerce-mercadopago'),
                        __('Chile', 'woocommerce-mercadopago'),
                        __('Colombia', 'woocommerce-mercadopago'),
                        __('Mexico', 'woocommerce-mercadopago'),
                        __('Peru', 'woocommerce-mercadopago'),
                        __('or', 'woocommerce-mercadopago'),
                        __('Venezuela', 'woocommerce-mercadopago')
                    );

                    // Sponsor ID
                    $sponsor_id = get_option('_mp_sponsor_id', '');
                    $sponsor_id_message = __('With this number we identify all your transactions and we know how many sales were processed by your account', 'woocommerce-mercadopago');

                    // Currency conversion.
                    $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
                    if (empty($_mp_currency_conversion_v1)) {
                        $is_currency_conversion_v1 = '';
                    } else {
                        $is_currency_conversion_v1 = 'checked="checked"';
                    }
                    $_can_do_currency_conversion_v1 = get_option('_can_do_currency_conversion_v1', false);
                    if (!empty($site_id_v1)) {
                        if (!WC_WooMercadoPago_Module::is_supported_currency($site_id_v1)) {
                            if (empty($_mp_currency_conversion_v1)) {
                                $currency_conversion_v1_message = WC_WooMercadoPago_Module::build_currency_not_converted_msg(
                                    WC_WooMercadoPago_Module::$country_configs[$site_id_v1]['currency'],
                                    WC_WooMercadoPago_Module::get_country_name($site_id_v1)
                                );
                            } elseif (!empty($_mp_currency_conversion_v1) && $_can_do_currency_conversion_v1) {
                                $currency_conversion_v1_message = WC_WooMercadoPago_Module::build_currency_converted_msg(
                                    WC_WooMercadoPago_Module::$country_configs[$site_id_v1]['currency']
                                );
                            } else {
                                $currency_conversion_v1_message = WC_WooMercadoPago_Module::build_currency_conversion_err_msg(
                                    WC_WooMercadoPago_Module::$country_configs[$site_id_v1]['currency']
                                );
                            }
                        } else {
                            $currency_conversion_v1_message = '';
                        }
                    } else {
                        $currency_conversion_v1_message = '';
                    }

                    require_once(dirname(__FILE__) . '/../../templates/mp_main_settings.php');
                }
            );
        });
    }

}

new WC_WooMercadoPago_Main_Settings();