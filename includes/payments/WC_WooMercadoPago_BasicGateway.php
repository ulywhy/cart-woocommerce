<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 *
 * WC_WooMercadoPago_BasicGateway
 *
 */
class WC_WooMercadoPago_BasicGateway extends WC_WooMercadoPago_PaymentAbstract
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'woo-mercado-pago-basic';
        $this->method_title = __('Mercado Pago - Basic Checkout', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('Receive payments in a matter of minutes. We make it easy for you: just tell us what you want to collect and we’ll take care of the rest.');
        $this->method = get_option('method', 'redirect');
        $this->title = get_option('title', __('Mercado Pago - Basic Checkout', 'woocommerce-mercadopago'));
        $this->auto_return = get_option('auto_return', 'yes');
        $this->success_url = get_option('success_url', '');
        $this->failure_url = get_option('failure_url', '');
        $this->pending_url = get_option('pending_url', '');
        $this->installments = get_option('installments', '24');
        $this->gateway_discount = get_option('gateway_discount', 0);
        $this->ex_payments = $this->getExPayments();
        parent::__construct();
        $this->form_fields = $this->getFormFields('Basic');
        $this->two_cards_mode = $this->mp->check_two_cards();
        $this->hook = new WC_WooMercadoPago_Hook_Basic($this);
    }

    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        $this->init_form_fields();
        $form_fields = array();
        $form_fields['method'] = $this->field_method();
        $form_fields['checkout_navigation_title'] = $this->field_checkout_navigation_title();
        $form_fields['auto_return'] = $this->field_auto_return();
        $form_fields['success_url'] = $this->field_success_url();
        $form_fields['failure_url'] = $this->field_failure_url();
        $form_fields['pending_url'] = $this->field_pending_url();
        $form_fields['installments'] = $this->field_installments();

        foreach ($this->field_ex_payments() as $key => $value) {
            $form_fields[$key] = $value;
        }
        $form_fields['two_cards_mode'] = $this->field_two_cards_mode();
        $form_fields_abs = parent::getFormFields($label);

        $form_fields_merge = array_merge_recursive($form_fields_abs, $form_fields);

        return $form_fields_merge;
    }

    /**
     * @return array
     */
    private function getExPayments()
    {
        $ex_payments = array();
        $get_ex_payment_options = get_option('_all_payment_methods_v0', '');
        if (!empty($get_ex_payment_options)) {
            foreach ($get_ex_payment_options = explode(',', $get_ex_payment_options) as $get_ex_payment_option) {
                if (get_option('ex_payments_' . $get_ex_payment_option, 'yes') == 'no') {
                    $ex_payments[] = $get_ex_payment_option;
                }
            }
        }
        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_method()
    {
        $method = array(
            'title' => __('Integration Method', 'woocommerce-mercadopago'),
            'type' => 'select',
            'description' => __('Select how your clients should interact with Mercado Pago. Modal Window (inside your store), Redirect (Client is redirected to Mercado Pago), or iFrame (an internal window is embedded to the page layout).', 'woocommerce-mercadopago'),
            'default' => 'redirect',
            'options' => array(
                'redirect' => __('Redirect', 'woocommerce-mercadopago'),
                'modal' => __('Modal Window', 'woocommerce-mercadopago')
            )
        );
        return $method;
    }

    /**
     * @return array
     */
    public function field_checkout_navigation_title()
    {
        $checkout_navigation_title = array(
            'title' => __('Checkout Navigation: How checkout redirections will behave', 'woocommerce-mercadopago'),
            'type' => 'title'
        );
        return $checkout_navigation_title;
    }

    /**
     * @return array
     */
    public function field_auto_return()
    {
        $auto_return = array(
            'title' => __('Auto Return', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Automatic Return After Payment', 'woocommerce-mercadopago'),
            'default' => 'yes',
            'description' => __('After the payment, client is automatically redirected.', 'woocommerce-mercadopago'),
        );
        return $auto_return;
    }

    /**
     * @return array
     */
    public function field_success_url()
    {
        // Validate back URL.
        if (!empty($this->success_url) && filter_var($this->success_url, FILTER_VALIDATE_URL) === FALSE) {
            $success_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $success_back_url_message = __('Where customers should be redirected after a successful purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $success_url = array(
            'title' => __('Sucess URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $success_back_url_message,
            'default' => ''
        );
        return $success_url;
    }

    /**
     * @return array
     */
    public function field_failure_url()
    {
        if (!empty($this->failure_url) && filter_var($this->failure_url, FILTER_VALIDATE_URL) === FALSE) {
            $fail_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $fail_back_url_message = __('Where customers should be redirected after a failed purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $failure_url = array(
            'title' => __('Failure URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $fail_back_url_message,
            'default' => ''
        );
        return $failure_url;
    }

    /**
     * @return array
     */
    public function field_pending_url()
    {
        // Validate back URL.
        if (!empty($this->pending_url) && filter_var($this->pending_url, FILTER_VALIDATE_URL) === FALSE) {
            $pending_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $pending_back_url_message = __('Where customers should be redirected after a pending purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $pending_url = array(
            'title' => __('Pending URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $pending_back_url_message,
            'default' => ''
        );
        return $pending_url;
    }

    /**
     * @return array
     */
    public function field_installments()
    {
        $installments = array(
            'title' => __('Max installments', 'woocommerce-mercadopago'),
            'type' => 'select',
            'description' => __('Select the max number of installments for your customers.', 'woocommerce-mercadopago'),
            'default' => '24',
            'options' => array(
                '1' => __('1x installment', 'woocommerce-mercadopago'),
                '2' => __('2x installmens', 'woocommerce-mercadopago'),
                '3' => __('3x installmens', 'woocommerce-mercadopago'),
                '4' => __('4x installmens', 'woocommerce-mercadopago'),
                '5' => __('5x installmens', 'woocommerce-mercadopago'),
                '6' => __('6x installmens', 'woocommerce-mercadopago'),
                '10' => __('10x installmens', 'woocommerce-mercadopago'),
                '12' => __('12x installmens', 'woocommerce-mercadopago'),
                '15' => __('15x installmens', 'woocommerce-mercadopago'),
                '18' => __('18x installmens', 'woocommerce-mercadopago'),
                '24' => __('24x installmens', 'woocommerce-mercadopago')
            )
        );
        return $installments;
    }

    /**
     * @return array
     */
    public function field_ex_payments()
    {
        $ex_payments = array();

        $get_payment_methods = get_option('_all_payment_methods_v0', '');
        if (!empty($get_payment_methods)) {
            $get_payment_methods = explode(',', $get_payment_methods);
        }

        $count_payment = 0;

        foreach ($get_payment_methods as $payment_method) {
            $count_payment++;

            $element = array(
                'label' => $payment_method,
                'id' => 'woocommerce_mercadopago_' . $payment_method,
                'default' => 'yes',
                'type' => 'checkbox'
            );
            if ($count_payment == 1) {
                $element['title'] = __('Payment Methods Accepted', 'woocommerce-mercadopago');
            }
            if ($count_payment == count($get_payment_methods)) {
                $element['description'] = __('Unselect the payment methods that you <strong>don\'t</strong> want to receive with Mercado Pago.', 'woocommerce-mercadopago');
            }
            $ex_payments["ex_payments_" . $payment_method] = $element;
        }

        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_two_cards_mode()
    {
        $two_cards_mode = array(
            'title' => __('Two Cards Mode', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Payments with Two Cards', 'woocommerce-mercadopago'),
            'default' => ($this->two_cards_mode == 'active' ? 'yes' : 'no'),
            'description' => __('Your customer will be able to use two different cards to pay the order.', 'woocommerce-mercadopago')
        );
        return $two_cards_mode;
    }

    /**
     * Payment Fields
     */
    public function payment_fields()
    {
        if ( $description = $this->get_description() ) {
            echo wpautop( wptexturize( $description ) );
        }
        if ( $this->supports( 'default_credit_card_form' ) ) {
            $this->credit_card_form();
        }
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }

        if ('redirect' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'customer being redirected to Mercado Pago.');
            return array(
                'result' => 'success',
                'redirect' => $this->create_url($order)
            );
        } elseif ('modal' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'preparing to render Mercado Pago checkout view.');
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }
    }

    /**
     * @param $order
     * @return bool
     */
    public function create_url($order)
    {
        $preferences = new WC_WooMercadoPago_PreferenceBasic($order, $this->ex_payments, $this->installments);
        try {
            $checkout_info = $this->mp->create_preference(json_encode($preferences));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                $this->log->write_log(__FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } elseif (is_wp_error($checkout_info)) {
                $this->log->write_log(__FUNCTION__, 'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } else {
                $this->log->write_log(__FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if ($this->sandbox) {
                    return $checkout_info['response']['sandbox_init_point'];
                }
                return $checkout_info['response']['init_point'];
            }
        } catch (MercadoPagoException $ex) {
            $this->log->write_log(__FUNCTION__, 'payment creation failed with exception: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return false;
        }
    }


}
