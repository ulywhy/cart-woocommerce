<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 *
 * WC_WooMercadoPago_CustomGateway
 *
 */
class WC_WooMercadoPago_CustomGateway extends WC_WooMercadoPago_PaymentAbstract
{

    /**
     * Constructor.
     */
    public function __construct()
    {

        $this->id = 'woo-mercado-pago-custom';
        $this->method_title = __('Mercado Pago - Custom Checkout', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('We give you the possibility to adapt the payment experience you want to offer 100% in your website, mobile app or anywhere you want. You can build the design that best fits your business model, aiming to maximize conversion.');
        $this->title = get_option('title', __('Mercado Pago - Custom Checkout', 'woocommerce-mercadopago'));
        $this->coupon_mode = get_option('coupon_mode', 'no');
        $this->installments = get_option('installments', '24');
        parent::__construct();
        $this->form_fields = $this->getFormFields('Custom');
        $this->loadHooks();
    }

    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        $form_fields = array();
        $form_fields['enabled'] = $this->field_enabled('Custom');
        $form_fields['coupon_mode'] = $this->field_coupon_mode();
        $form_fields['title'] = $this->field_title();
        $form_fields = parent::getFormFields($label);
        return $form_fields;
    }

    /**
     *
     */
    public function loadHooks(){
        $hooks = new WC_WooMercadoPago_Hook_Custom($this);
        $hooks->loadHooks();
    }

    /**
     * @return array
     */
    public function field_coupon_mode()
    {
        return array(
            'title' => __('Coupons', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Enable coupons of discounts', 'woocommerce-mercadopago'),
            'default' => 'no',
            'description' => __('If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'woocommerce-mercadopago')
        );
    }



    public function define_settings_to_send()
    {
        $infra_data = WC_WooMercadoPago_Module::get_common_settings();
        $infra_data['checkout_custom_credit_card'] = ($this->settings['enabled'] == 'yes' ? 'true' : 'false');
        $infra_data['checkout_custom_credit_card_coupon'] = ($this->settings['coupon_mode'] == 'yes' ? 'true' : 'false');
        return $infra_data;
    }

    /*
     * ========================================================================
     * HANDLES ORDER
     * ========================================================================
	 */

    public function get_order_status($status_detail)
    {
        switch ($status_detail) {
            case 'accredited':
                return __('Done, your payment was accredited!', 'woocommerce-mercadopago');
            case 'pending_contingency':
                return __('We are processing the payment. In less than an hour we will e-mail you the results.', 'woocommerce-mercadopago');
            case 'pending_review_manual':
                return __('We are processing the payment. In less than 2 business days we will tell you by e-mail whether it has accredited or we need more information.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_card_number':
                return __('Check the card number.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_date':
                return __('Check the expiration date.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_other':
                return __('Check the information.', 'woocommerce-mercadopago');
            case 'cc_rejected_bad_filled_security_code':
                return __('Check the security code.', 'woocommerce-mercadopago');
            case 'cc_rejected_blacklist':
                return __('We could not process your payment.', 'woocommerce-mercadopago');
            case 'cc_rejected_call_for_authorize':
                return __('You must authorize the payment of your orders.', 'woocommerce-mercadopago');
            case 'cc_rejected_card_disabled':
                return __('Call your card issuer to activate your card. The phone is on the back of your card.', 'woocommerce-mercadopago');
            case 'cc_rejected_card_error':
                return __('We could not process your payment.', 'woocommerce-mercadopago');
            case 'cc_rejected_duplicated_payment':
                return __('You already made a payment for that amount. If you need to repay, use another card or other payment method.', 'woocommerce-mercadopago');
            case 'cc_rejected_high_risk':
                return __('Your payment was rejected. Choose another payment method. We recommend cash.', 'woocommerce-mercadopago');
            case 'cc_rejected_insufficient_amount':
                return __('Your payment do not have sufficient funds.', 'woocommerce-mercadopago');
            case 'cc_rejected_invalid_installments':
                return __('Your payment does not process payments with selected installments.', 'woocommerce-mercadopago');
            case 'cc_rejected_max_attempts':
                return __('You have reached the limit of allowed attempts. Choose another card or another payment method.', 'woocommerce-mercadopago');
            case 'cc_rejected_other_reason':
                return __('This payment method did not process the payment.', 'woocommerce-mercadopago');
            default:
                return __('This payment method did not process the payment.', 'woocommerce-mercadopago');
        }
    }

    /*
	 * ========================================================================
	 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
	 * ========================================================================
	 */

    public function payment_fields()
    {

        exit('Michel');
        wp_enqueue_script('wc-credit-card-form');

        $amount = $this->get_order_total();
        $logged_user_email = (wp_get_current_user()->ID != 0) ? wp_get_current_user()->user_email : null;
        $customer = isset($logged_user_email) ? $this->mp->get_or_create_customer($logged_user_email) : null;
        $discount_action_url = get_site_url() . '/index.php/woocommerce-mercadopago/?wc-api=' . get_class($this);

        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        $banner_url = get_option('_mp_custom_banner');
        if (!isset($banner_url) || empty($banner_url)) {
            $banner_url = $this->site_data['checkout_banner_custom'];
        }

        $parameters = array(
            'amount' => $amount,
            // ===
            'site_id' => get_option('_site_id_v1'),
            'public_key' => get_option('_mp_public_key'),
            'coupon_mode' => isset($logged_user_email) ? $this->coupon_mode : 'no',
            'discount_action_url' => $discount_action_url,
            'payer_email' => $logged_user_email,
            // ===
            'images_path' => plugins_url('../assets/images/', plugin_dir_path(__FILE__)),
            'banner_path' => $banner_url,
            'customer_cards' => isset($customer) ? (isset($customer['cards']) ? $customer['cards'] : array()) : array(),
            'customerId' => isset($customer) ? (isset($customer['id']) ? $customer['id'] : null) : null,
            'currency_ratio' => $currency_ratio,
            'woocommerce_currency' => get_woocommerce_currency(),
            'account_currency' => $this->site_data['currency'],
            // ===
            'path_to_javascript' => plugins_url('../assets/js/credit-card.js', plugin_dir_path(__FILE__))
        );
        wc_get_template(
            'credit-card/payment-form.php',
            $parameters,
            'woo/mercado/pago/module/',
            WC_WooMercadoPago_Module::get_templates_path()
        );
    }

    /**
     * ========================================================================
     * PROCESS PAYMENT.
     * ========================================================================
     *
     * Summary: Handle the payment and processing the order.
     * Description: This function is called after we click on [place_order] button, and each field is
     * passed to this function through $_POST variable.
     * @return an array containing the result of the processment and the URL to redirect.
     */
    public function process_payment($order_id)
    {
        if (!isset($_POST['mercadopago_custom'])) {
            return;
        }
        $custom_checkout = $_POST['mercadopago_custom'];
        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }
        // Mexico country case.
        if (!isset($custom_checkout['paymentMethodId']) || empty($custom_checkout['paymentMethodId'])) {
            $custom_checkout['paymentMethodId'] = $custom_checkout['paymentMethodSelector'];
        }
        if (isset($custom_checkout['amount']) && !empty($custom_checkout['amount']) &&
            isset($custom_checkout['token']) && !empty($custom_checkout['token']) &&
            isset($custom_checkout['paymentMethodId']) && !empty($custom_checkout['paymentMethodId']) &&
            isset($custom_checkout['installments']) && !empty($custom_checkout['installments']) &&
            $custom_checkout['installments'] != -1) {
            $response = $this->create_url($order, $custom_checkout);

            // Check for card save.
            if (method_exists($order, 'update_meta_data')) {
                if (isset($custom_checkout['doNotSaveCard'])) {
                    $order->update_meta_data('_save_card', 'no');
                } else {
                    $order->update_meta_data('_save_card', 'yes');
                }
                $order->save();
            } else {
                if (isset($custom_checkout['doNotSaveCard'])) {
                    update_post_meta($order_id, '_save_card', 'no');
                } else {
                    update_post_meta($order_id, '_save_card', 'yes');
                }
            }
            // Switch on response.
            if (array_key_exists('status', $response)) {
                switch ($response['status']) {
                    case 'approved':
                        WC()->cart->empty_cart();
                        wc_add_notice(
                            '<p>' . $this->get_order_status('accredited') . '</p>',
                            'notice'
                        );
                        $order->add_order_note(
                            'Mercado Pago: ' . __('Payment approved.', 'woocommerce-mercadopago')
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'pending':
                        // Order approved/pending, we just redirect to the thankyou page.
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'in_process':
                        // For pending, we don't know if the purchase will be made, so we must inform this status.
                        WC()->cart->empty_cart();
                        wc_add_notice(
                            '<p>' . $this->get_order_status($response['status_detail']) . '</p>' .
                            '<p><a class="button" href="' . esc_url($order->get_checkout_order_received_url()) . '">' .
                            __('Check your order resume', 'woocommerce-mercadopago') .
                            '</a></p>',
                            'notice'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url(true)
                        );
                        break;
                    case 'rejected':
                        // If rejected is received, the order will not proceed until another payment try, so we must inform this status.
                        wc_add_notice(
                            '<p>' . __('Your payment was refused. You can try again.', 'woocommerce-mercadopago') . '<br>' .
                            $this->get_order_status($response['status_detail']) .
                            '</p>' .
                            '<p><a class="button" href="' . esc_url($order->get_checkout_payment_url()) . '">' .
                            __('Click to try again', 'woocommerce-mercadopago') .
                            '</a></p>',
                            'error'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url(true)
                        );
                        break;
                    case 'cancelled':
                    case 'in_mediation':
                    case 'charged-back':
                        // If we enter here (an order generating a direct [cancelled, in_mediation, or charged-back] status),
                        // them there must be something very wrong!
                        break;
                    default:
                        break;
                }
            } else {
                // Process when fields are imcomplete.
                wc_add_notice(
                    '<p>' .
                    __('A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form?', 'woocommerce-mercadopago') . ' MERCADO PAGO: ' .
                    WC_WooMercadoPago_Module::get_common_error_messages($response) .
                    '</p>',
                    'error'
                );
                return array(
                    'result' => 'fail',
                    'redirect' => '',
                );
            }
        } else {
            // Process when fields are imcomplete.
            wc_add_notice(
                '<p>' .
                __('A problem was occurred when processing your payment. Please, try again.', 'woocommerce-mercadopago') .
                '</p>',
                'error'
            );
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }

    protected function create_url($order, $custom_checkout)
    {
        // Creates the order parameters by checking the cart configuration.
        $preferences = new WC_WooMercadoPago_PreferenceCustom($order, $custom_checkout);
        // Checks for sandbox mode.
        $this->mp->sandbox_mode($this->sandbox);
        // Create order preferences with Mercado Pago API request.
        try {
            $checkout_info = $this->mp->post('/v1/payments', json_encode($preferences));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                // Mercado Pago throwed an error.
                $this->write_log(
                    __FUNCTION__,
                    'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']
                );
                return $checkout_info['response']['message'];
            } elseif (is_wp_error($checkout_info)) {
                // WordPress throwed an error.
                $this->write_log(
                    __FUNCTION__,
                    'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']
                );
                return $checkout_info['response']['message'];
            } else {
                // Obtain the URL.
                $this->write_log(
                    __FUNCTION__,
                    'payment link generated with success from mercado pago, with structure as follow: ' .
                    json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
                // TODO: Verify sandbox availability.
                //if ( 'yes' == $this->sandbox ) {
                //	return $checkout_info['response']['sandbox_init_point'];
                //} else {
                return $checkout_info['response'];
                //}
            }
        } catch (MercadoPagoException $ex) {
            // Something went wrong with the payment creation.
            $this->write_log(
                __FUNCTION__,
                'payment creation failed with exception: ' .
                json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
            return $ex->getMessage();
        }
    }

    /**
     * Summary: Check if we have existing customer card, if not we create and save it.
     * Description: Check if we have existing customer card, if not we create and save it.
     * @return boolean true/false depending on the validation result.
     */
    public function check_and_save_customer_card($checkout_info)
    {
        $this->write_log(
            __FUNCTION__,
            'checking info to create card: ' .
            json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        $custId = null;
        $token = null;
        $issuer_id = null;
        $payment_method_id = null;
        if (isset($checkout_info['payer']['id']) && !empty($checkout_info['payer']['id'])) {
            $custId = $checkout_info['payer']['id'];
        } else {
            return;
        }
        if (isset($checkout_info['metadata']['token']) && !empty($checkout_info['metadata']['token'])) {
            $token = $checkout_info['metadata']['token'];
        } else {
            return;
        }
        if (isset($checkout_info['issuer_id']) && !empty($checkout_info['issuer_id'])) {
            $issuer_id = (integer)($checkout_info['issuer_id']);
        }
        if (isset($checkout_info['payment_method_id']) && !empty($checkout_info['payment_method_id'])) {
            $payment_method_id = $checkout_info['payment_method_id'];
        }
        try {
            $this->mp->create_card_in_customer($custId, $token, $payment_method_id, $issuer_id);
        } catch (MercadoPagoException $ex) {
            $this->write_log(
                __FUNCTION__,
                'card creation failed: ' .
                json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    /*
	 * ========================================================================
	 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
	 * ========================================================================
	 */

    // Enter a gateway method-specific rule within this function
    public function mp_config_rule_is_available()
    {
        $_mp_access_token = get_option('_mp_access_token');
        $_mp_debug_mode = get_option('_mp_debug_mode', '');
        $is_prod_credentials = strpos($_mp_access_token, 'TEST') === false;

        // If we do not have SSL in production environment, we are not allowed to process.
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
            if (empty ($_mp_debug_mode)) {
                return false;
            }
        } elseif ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') && $is_prod_credentials) {
            // If we don't have SSL, we can only enable this payment method with TEST credentials.
            return false;
        } else {
            return true;
        }
    }

    /*
	 * ========================================================================
	 * IPN MECHANICS (SERVER SIDE)
	 * ========================================================================
	 */

    /**
     * @throws MercadoPagoException
     */
    public function check_ipn_response()
    {
        $customNotification = WC_WooMercadoPago_Notification_Custom::getNotificationCustomInstance();
        $customNotification->check_ipn_response();
    }
}
