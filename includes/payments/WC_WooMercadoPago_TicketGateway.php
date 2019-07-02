<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_WooMercadoPago_TicketGateway
 */
class WC_WooMercadoPago_TicketGateway extends WC_WooMercadoPago_PaymentAbstract
{
    CONST ID = 'woo-mercado-pago-ticket';

    /**
     * WC_WooMercadoPago_TicketGateway constructor.
     * @throws WC_WooMercadoPago_Exception
     */
    public function __construct()
    {
        $this->id = 'woo-mercado-pago-ticket';

        if (!$this->validateSection()) {
            return;
        }

        $this->form_fields = array();
        $this->method_title = __('Mercado Pago - Checkout personalizado', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('Acepta medios de pago en efectivo y amplía las opciones de compra de tus clientes.');
        $this->title = __('Paga con medios de pago en efectivo', 'woocommerce-mercadopago');
        $this->coupon_mode = $this->getOption('coupon_mode', 'no');
        $this->installments = $this->getOption('installments', '24');
        $this->stock_reduce_mode = $this->getOption('stock_reduce_mode', 'no');
        $this->date_expiration = $this->getOption('date_expiration', 3);
        $this->payment_type = "ticket";
        $this->checkout_type = "custom";
        $this->field_forms_order = array();
        parent::__construct();
        $this->form_fields = $this->getFormFields('Ticket');
        $this->hook = new WC_WooMercadoPago_Hook_Ticket($this);
    }


    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        if (is_admin()) {
            wp_enqueue_script('woocommerce-mercadopago-ticket-config-script', plugins_url('../assets/js/custom_config_mercadopago.js', plugin_dir_path(__FILE__)));
        }

        if (empty($this->checkout_country)) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 7);
        }

        if(!empty($this->checkout_country) && empty($this->mp_access_token_test) && empty($this->mp_access_token_prod)) {
            $this->field_forms_order = array_slice($this->field_forms_order, 0, 22);
        }

        $form_fields = array();
        if (!empty($this->checkout_country) && !empty($this->mp_access_token_test) && !empty($this->mp_access_token_prod)) {
             //$form_fields['installments'] = $this->field_installments();
        }

        $form_fields_abs = parent::getFormFields($label);
        if (count($form_fields_abs) == 1) {
            return $form_fields_abs;
        }
        $form_fields_merge = array_merge($form_fields_abs, $form_fields);
        $fields = $this->sortFormFields($form_fields_merge, $this->field_forms_order);

        return $fields;
    }

    /**
     * @param $order_id
     */
//    public function show_ticket_script($order_id)
//    {
//        $order = wc_get_order($order_id);
//        $transaction_details = (method_exists($order, 'get_meta')) ? $order->get_meta('_transaction_details_ticket') : get_post_meta($order->get_id(), '_transaction_details_ticket', true);
//        if (empty($transaction_details)) {
//            return;
//        }
//
//        $html = '<p>' .
//            __('Thank you for your order. Please, pay the ticket to get your order approved.', 'woocommerce-mercadopago') .
//            '</p>' .
//            '<p><iframe src="' . $transaction_details . '" style="width:100%; height:1000px;"></iframe></p>' .
//            '<a id="submit-payment" target="_blank" href="' . $transaction_details . '" class="button alt"' .
//            ' style="font-size:1.25rem; width:75%; height:48px; line-height:24px; text-align:center;">' .
//            __('Print the Ticket', 'woocommerce-mercadopago') .
//            '</a> ';
//        $added_text = '<p>' . $html . '</p>';
//        echo $added_text;
//    }

    /**
     *
     */
    public function payment_fields()
    {
        $amount = $this->get_order_total();
        $logged_user_email = (wp_get_current_user()->ID != 0) ? wp_get_current_user()->user_email : null;
        $discount_action_url = get_site_url() . '/index.php/woocommerce-mercadopago/?wc-api=WC_WooMercadoPago_TicketGateway';
        $address = get_user_meta(wp_get_current_user()->ID, 'shipping_address_1', true);
        $address_2 = get_user_meta(wp_get_current_user()->ID, 'shipping_address_2', true);
        $address .= (!empty($address_2) ? ' - ' . $address_2 : '');
        $country = get_user_meta(wp_get_current_user()->ID, 'shipping_country', true);
        $address .= (!empty($country) ? ' - ' . $country : '');

        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = $this->getOption('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        $parameters = array(
            'amount' => $amount,
            'payment_methods' => json_decode(get_option('_all_payment_methods_ticket', '[]'), true),
            'site_id' => $this->getOption('_site_id_v1'),
            'coupon_mode' => isset($logged_user_email) ? $this->coupon_mode : 'no',
            'discount_action_url' => $discount_action_url,
            'payer_email' => $logged_user_email,
            'images_path' => plugins_url('../assets/images/', plugin_dir_path(__FILE__)),
            'currency_ratio' => $currency_ratio,
            'woocommerce_currency' => get_woocommerce_currency(),
            'account_currency' => $this->site_data['currency'],
            'febraban' => (wp_get_current_user()->ID != 0) ?
                array(
                    'firstname' => wp_get_current_user()->user_firstname,
                    'lastname' => wp_get_current_user()->user_lastname,
                    'docNumber' => '',
                    'address' => $address,
                    'number' => '',
                    'city' => get_user_meta(wp_get_current_user()->ID, 'shipping_city', true),
                    'state' => get_user_meta(wp_get_current_user()->ID, 'shipping_state', true),
                    'zipcode' => get_user_meta(wp_get_current_user()->ID, 'shipping_postcode', true)
                ) :
                array(
                    'firstname' => '', 'lastname' => '', 'docNumber' => '', 'address' => '', 'number' => '', 'city' => '', 'state' => '', 'zipcode' => ''
                ),
            'path_to_javascript' => plugins_url('../assets/js/ticket.js', plugin_dir_path(__FILE__))
        );

        wc_get_template('ticket/ticket-form.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path());
    }

    /**
     * @param $order_id
     * @return array|void
     */
    public function process_payment($order_id)
    {
        $ticket_checkout = apply_filters('wc_mercadopagoticket_ticket_checkout', $_POST['mercadopago_ticket']);

        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }

        // Check for brazilian FEBRABAN rules.
        if ($this->getOption('_site_id_v1') == 'MLB') {
            if (!isset($ticket_checkout['firstname']) || empty($ticket_checkout['firstname']) ||
                !isset($ticket_checkout['lastname']) || empty($ticket_checkout['lastname']) ||
                !isset($ticket_checkout['docNumber']) || empty($ticket_checkout['docNumber']) ||
                (strlen($ticket_checkout['docNumber']) != 14 && strlen($ticket_checkout['docNumber']) != 18) ||
                !isset($ticket_checkout['address']) || empty($ticket_checkout['address']) ||
                !isset($ticket_checkout['number']) || empty($ticket_checkout['number']) ||
                !isset($ticket_checkout['city']) || empty($ticket_checkout['city']) ||
                !isset($ticket_checkout['state']) || empty($ticket_checkout['state']) ||
                !isset($ticket_checkout['zipcode']) || empty($ticket_checkout['zipcode'])) {
                wc_add_notice(
                    '<p>' .
                    __('A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form?', 'woocommerce-mercadopago') .
                    '</p>',
                    'error'
                );
                return array(
                    'result' => 'fail',
                    'redirect' => '',
                );
            }
        }

        if (isset($ticket_checkout['amount']) && !empty($ticket_checkout['amount']) &&
            isset($ticket_checkout['paymentMethodId']) && !empty($ticket_checkout['paymentMethodId'])) {
            $response = $this->create_preference($order, $ticket_checkout);
            if (array_key_exists('status', $response)) {
                if ($response['status'] == 'pending') {
                    if ($response['status_detail'] == 'pending_waiting_payment') {
                        WC()->cart->empty_cart();
                        if ($this->stock_reduce_mode == 'yes') {
                            $order->reduce_order_stock();
                        }
                        // WooCommerce 3.0 or later.
                        if (method_exists($order, 'update_meta_data')) {
                            $order->update_meta_data('_transaction_details_ticket', $response['transaction_details']['external_resource_url']);
                            $order->save();
                        } else {
                            update_post_meta($order->get_id(), '_transaction_details_ticket', $response['transaction_details']['external_resource_url']);
                        }
                        // Shows some info in checkout page.
                        $order->add_order_note(
                            'Mercado Pago: ' .
                            __('Customer haven\'t paid yet.', 'woocommerce-mercadopago')
                        );
                        $order->add_order_note(
                            'Mercado Pago: ' .
                            __('To reprint the ticket click ', 'woocommerce-mercadopago') .
                            '<a target="_blank" href="' .
                            $response['transaction_details']['external_resource_url'] . '">' .
                            __('here', 'woocommerce-mercadopago') .
                            '</a>', 1, false
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                    }
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

    /**
     * @param $order
     * @param $ticket_checkout
     * @return string
     */
    public function create_preference($order, $ticket_checkout)
    {
        $preferencesTicket = new WC_WooMercadoPago_PreferenceTicket($order, $ticket_checkout);
        $preferences = $preferencesTicket->get_preference();
        try {
            $checkout_info = $this->mp->post('/v1/payments', json_encode($preferences));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                $this->log->write_log(__FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return $checkout_info['response']['message'];
            } elseif (is_wp_error($checkout_info)) {
                $this->log->write_log(__FUNCTION__, 'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return $checkout_info['response']['message'];
            } else {
                $this->log->write_log(__FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return $checkout_info['response'];
            }
        } catch (WC_WooMercadoPago_Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'payment creation failed with exception: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $ex->getMessage();
        }
    }

    /**
     * @return bool
     */
    public function mp_config_rule_is_available()
    {
        // Check if there are available payments with ticket.
        $payment_methods = json_decode(get_option('_all_payment_methods_ticket', '[]'), true);
        if (count($payment_methods) == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function is_available()
    {
        if (!parent::is_available()) {
            return false;
        }

        $payment_methods = json_decode(get_option('_all_payment_methods_ticket', '[]'), true);
        if (count($payment_methods) == 0) {
            return false;
        }

        return true;
    }
}