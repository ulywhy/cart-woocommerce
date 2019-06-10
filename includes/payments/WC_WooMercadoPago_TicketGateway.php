<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class WC_WooMercadoPago_TicketGateway
 */
class WC_WooMercadoPago_TicketGateway extends WC_WooMercadoPago_PaymentAbstract
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'woo-mercado-pago-ticket';
        $this->method_title = __('Mercado Pago - Ticket', 'woocommerce-mercadopago');
        $this->method_description = $this->getMethodDescription('We give you the possibility to adapt the payment experience you want to offer 100% in your website, mobile app or anywhere you want. You can build the design that best fits your business model, aiming to maximize conversion.');
        $this->title = get_option('title', __('Mercado Pago - Ticket', 'woocommerce-mercadopago'));
        $this->coupon_mode = get_option('coupon_mode', 'no');
        $this->installments = get_option('installments', '24');
        $this->stock_reduce_mode = get_option('stock_reduce_mode', 'no');
        $this->date_expiration = get_option('date_expiration', 3);
        $this->payment_type = "ticket";
        $this->checkout_type = "custom";
        parent::__construct();
        $this->form_fields = $this->getFormFields('Ticket');
        $this->admin_notices();
        $this->hook = new WC_WooMercadoPago_Hook_Ticket($this);
    }

    /**
     * Admin Notices
     */
    public function admin_notices()
    {
        if (is_admin()) {
            // Show message if credentials are not properly configured.
            $_site_id_v1 = get_option('_site_id_v1', '');
            if (empty($_site_id_v1)) {
                add_action('admin_notices', array($this, 'credential_missing_message'));
                $this->form_fields = array();
            }
        }
    }

    public function show_ticket_script($order_id)
    {
        $order = wc_get_order($order_id);
        $used_gateway = (method_exists($order, 'get_meta')) ? $order->get_meta('_used_gateway') : get_post_meta($order->id, '_used_gateway', true);
        $transaction_details = (method_exists($order, 'get_meta')) ? $order->get_meta('_transaction_details_ticket') : get_post_meta($order->id, '_transaction_details_ticket', true);

        // A watchdog to prevent operations from other gateways.
        if ($used_gateway != 'WC_WooMercadoPago_TicketGateway' || empty($transaction_details)) {
            return;
        }

        $html = '<p>' .
            __('Thank you for your order. Please, pay the ticket to get your order approved.', 'woocommerce-mercadopago') .
            '</p>' .
            '<p><iframe src="' . $transaction_details . '" style="width:100%; height:1000px;"></iframe></p>' .
            '<a id="submit-payment" target="_blank" href="' . $transaction_details . '" class="button alt"' .
            ' style="font-size:1.25rem; width:75%; height:48px; line-height:24px; text-align:center;">' .
            __('Print the Ticket', 'woocommerce-mercadopago') .
            '</a> ';
        $added_text = '<p>' . $html . '</p>';
        echo $added_text;
    }



    public function payment_fields()
    {

        $amount = $this->get_order_total();
        $logged_user_email = (wp_get_current_user()->ID != 0) ? wp_get_current_user()->user_email : null;
        $customer = isset($logged_user_email) ? $this->mp->get_or_create_customer($logged_user_email) : null;
        $discount_action_url = get_site_url() . '/index.php/woocommerce-mercadopago/?wc-api=WC_WooMercadoPago_TicketGateway';
        $address = get_user_meta(wp_get_current_user()->ID, 'shipping_address_1', true);
        $address_2 = get_user_meta(wp_get_current_user()->ID, 'shipping_address_2', true);
        $address .= (!empty($address_2) ? ' - ' . $address_2 : '');
        $country = get_user_meta(wp_get_current_user()->ID, 'shipping_country', true);
        $address .= (!empty($country) ? ' - ' . $country : '');

        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        $parameters = array(
            'amount' => $amount,
            'payment_methods' => json_decode(get_option('_all_payment_methods_ticket', '[]'), true),
            // ===
            'site_id' => get_option('_site_id_v1'),
            'coupon_mode' => isset($logged_user_email) ? $this->coupon_mode : 'no',
            'discount_action_url' => $discount_action_url,
            'payer_email' => $logged_user_email,
            // ===
            'images_path' => plugins_url('../../assets/images/', plugin_dir_path(__FILE__)),
            'currency_ratio' => $currency_ratio,
            'woocommerce_currency' => get_woocommerce_currency(),
            'account_currency' => $this->site_data['currency'],
            // ===
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
                    'firstname' => '', 'lastname' => '', 'docNumber' => '', 'address' => '',
                    'number' => '', 'city' => '', 'state' => '', 'zipcode' => ''
                ),
            'path_to_javascript' => plugins_url('../../assets/js/ticket.js', plugin_dir_path(__FILE__))
        );

        wc_get_template(
            'ticket/ticket-form.php',
            $parameters,
            'woo/mercado/pago/module/',
            WC_WooMercadoPago_Module::get_templates_path()
        );
    }

    /**
     * @param $order_id
     * @return array|void
     */
    public function process_payment($order_id)
    {

        if (!isset($_POST['mercadopago_ticket'])) {
            return;
        }
        $ticket_checkout = apply_filters('wc_mercadopagoticket_ticket_checkout', $_POST['mercadopago_ticket']);

        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }

        // Check for brazilian FEBRABAN rules.
        if (get_option('_site_id_v1') == 'MLB') {
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
            $response = $this->create_url($order, $ticket_checkout);
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
                            update_post_meta(
                                $order->id,
                                '_transaction_details_ticket',
                                $response['transaction_details']['external_resource_url']
                            );
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
     * Summary: Build Mercado Pago preference.
     * Description: Create Mercado Pago preference and get init_point URL based in the order options
     * from the cart.
     * @return the preference object.
     */
    private function build_payment_preference($order, $ticket_checkout)
    {

        // A string to register items (workaround to deal with API problem that shows only first item).
        $items = array();
        $order_total = 0;
        $list_of_items = array();

        // Find currency rate.
        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        // Here we build the array that contains ordered items, from customer cart.
        if (sizeof($order->get_items()) > 0) {
            foreach ($order->get_items() as $item) {
                if ($item['qty']) {
                    $product = new WC_product($item['product_id']);
                    $product_title = method_exists($product, 'get_description') ?
                        $product->get_name() :
                        $product->post->post_title;
                    $product_content = method_exists($product, 'get_description') ?
                        $product->get_description() :
                        $product->post->post_content;
                    // Calculates line amount and discounts.
                    $line_amount = $item['line_total'] + $item['line_tax'];
                    $discount_by_gateway = (float)$line_amount * ($this->gateway_discount / 100);
                    $order_total += ($line_amount - $discount_by_gateway);
                    // Add the item.
                    array_push($list_of_items, $product_title . ' x ' . $item['qty']);
                    array_push($items, array(
                        'id' => $item['product_id'],
                        'title' => html_entity_decode($product_title) . ' x ' . $item['qty'],
                        'description' => sanitize_file_name(html_entity_decode(
                            strlen($product_content) > 230 ?
                                substr($product_content, 0, 230) . '...' :
                                $product_content
                        )),
                        'picture_url' => sizeof($order->get_items()) > 1 ?
                            plugins_url('assets/images/cart.png', plugin_dir_path(__FILE__)) :
                            wp_get_attachment_url($product->get_image_id()),
                        'category_id' => get_option('_mp_category_name', 'others'),
                        'quantity' => 1,
                        'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                            floor(($line_amount - $discount_by_gateway) * $currency_ratio) :
                            floor(($line_amount - $discount_by_gateway) * $currency_ratio * 100) / 100
                    ));
                }
            }
        }

        // Creates the shipment cost structure.
        $ship_cost = ($order->get_total_shipping() + $order->get_shipping_tax());
        if ($ship_cost > 0) {
            $order_total += $ship_cost;
            $item = array(
                'title' => method_exists($order, 'get_id') ?
                    $order->get_shipping_method() :
                    $order->shipping_method,
                'description' => __('Shipping service used by store', 'woocommerce-mercadopago'),
                'category_id' => get_option('_mp_category_name', 'others'),
                'quantity' => 1,
                'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                    floor($ship_cost * $currency_ratio) :
                    floor($ship_cost * $currency_ratio * 100) / 100
            );
            $items[] = $item;
        }

        // Discounts features.
        if (isset($ticket_checkout['discount']) && !empty($ticket_checkout['discount']) &&
            isset($ticket_checkout['coupon_code']) && !empty($ticket_checkout['coupon_code']) &&
            $ticket_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-ticket') {
            $item = array(
                'title' => __('Discount provided by store', 'woocommerce-mercadopago'),
                'description' => __('Discount provided by store', 'woocommerce-mercadopago'),
                'category_id' => get_option('_mp_category_name', 'others'),
                'quantity' => 1,
                'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                    -floor($ticket_checkout['discount'] * $currency_ratio) :
                    -floor($ticket_checkout['discount'] * $currency_ratio * 100) / 100
            );
            $items[] = $item;
        }

        // Build additional information from the customer data.
        $payer_additional_info = array(
            'first_name' => (method_exists($order, 'get_id') ?
                html_entity_decode($order->get_billing_first_name()) :
                html_entity_decode($order->billing_first_name)),
            'last_name' => (method_exists($order, 'get_id') ?
                html_entity_decode($order->get_billing_last_name()) :
                html_entity_decode($order->billing_last_name)),
            //'registration_date' =>
            'phone' => array(
                //'area_code' =>
                'number' => (method_exists($order, 'get_id') ?
                    $order->get_billing_phone() :
                    $order->billing_phone)
            ),
            'address' => array(
                'zip_code' => (method_exists($order, 'get_id') ?
                    $order->get_billing_postcode() :
                    $order->billing_postcode
                ),
                //'street_number' =>
                'street_name' => html_entity_decode(method_exists($order, 'get_id') ?
                    $order->get_billing_address_1() . ' / ' .
                    $order->get_billing_city() . ' ' .
                    $order->get_billing_state() . ' ' .
                    $order->get_billing_country() :
                    $order->billing_address_1 . ' / ' .
                    $order->billing_city . ' ' .
                    $order->billing_state . ' ' .
                    $order->billing_country
                )
            )
        );

        // Create the shipment address information set.
        $shipments = array(
            'receiver_address' => array(
                'zip_code' => method_exists($order, 'get_id') ?
                    $order->get_shipping_postcode() :
                    $order->shipping_postcode,
                //'street_number' =>
                'street_name' => html_entity_decode(method_exists($order, 'get_id') ?
                    $order->get_shipping_address_1() . ' ' .
                    $order->get_shipping_address_2() . ' ' .
                    $order->get_shipping_city() . ' ' .
                    $order->get_shipping_state() . ' ' .
                    $order->get_shipping_country() :
                    $order->shipping_address_1 . ' ' .
                    $order->shipping_address_2 . ' ' .
                    $order->shipping_city . ' ' .
                    $order->shipping_state . ' ' .
                    $order->shipping_country
                ),
                //'floor' =>
                'apartment' => method_exists($order, 'get_id') ?
                    $order->get_shipping_address_2() :
                    $order->shipping_address_2
            )
        );

        // Build the expiration date string.
        $date_of_expiration = date('Y-m-d', strtotime('+' . $this->date_expiration . ' days')) . 'T00:00:00.000-00:00';

        // The payment preference.
        $preferences = array(
            'date_of_expiration' => $date_of_expiration,
            'transaction_amount' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                floor($order_total * $currency_ratio) :
                floor($order_total * $currency_ratio * 100) / 100,
            'description' => implode(', ', $list_of_items),
            'payment_method_id' => $ticket_checkout['paymentMethodId'],
            'payer' => array(
                'email' => method_exists($order, 'get_id') ?
                    $order->get_billing_email() :
                    $order->billing_email
            ),
            'external_reference' => get_option('_mp_store_identificator', 'WC-') .
                (method_exists($order, 'get_id') ? $order->get_id() : $order->id),
            'statement_descriptor' => get_option('_mp_statement_descriptor', 'Mercado Pago'),
            //'binary_mode' => ( $this->binary_mode == 'yes' ),
            'additional_info' => array(
                'items' => $items,
                'payer' => $payer_additional_info,
                'shipments' => $shipments
            )
        );

        // FEBRABAN rules.
        if ($this->site_data['currency'] == 'BRL') {
            $preferences['payer']['first_name'] = $ticket_checkout['firstname'];
            $preferences['payer']['last_name'] = strlen($ticket_checkout['docNumber']) == 14 ? $ticket_checkout['lastname'] : $ticket_checkout['firstname'];
            $preferences['payer']['identification']['type'] = strlen($ticket_checkout['docNumber']) == 14 ? 'CPF' : 'CNPJ';
            $preferences['payer']['identification']['number'] = $ticket_checkout['docNumber'];
            $preferences['payer']['address']['street_name'] = $ticket_checkout['address'];
            $preferences['payer']['address']['street_number'] = $ticket_checkout['number'];
            $preferences['payer']['address']['neighborhood'] = $ticket_checkout['city'];
            $preferences['payer']['address']['city'] = $ticket_checkout['city'];
            $preferences['payer']['address']['federal_unit'] = $ticket_checkout['state'];
            $preferences['payer']['address']['zip_code'] = $ticket_checkout['zipcode'];
        }

        // Do not set IPN url if it is a localhost.
        if (!strrpos(get_site_url(), 'localhost')) {
            $notification_url = get_option('_mp_custom_domain', '');
            // Check if we have a custom URL.
            if (empty($notification_url) || filter_var($notification_url, FILTER_VALIDATE_URL) === FALSE) {
                $preferences['notification_url'] = WC()->api_request_url('WC_WooMercadoPago_TicketGateway');
            } else {
                $preferences['notification_url'] = WC_WooMercadoPago_Module::fix_url_ampersand(esc_url(
                    $notification_url . '/wc-api/WC_WooMercadoPago_TicketGateway/'
                ));
            }
        }

        // Discounts features.
        if (isset($ticket_checkout['discount']) && !empty($ticket_checkout['discount']) &&
            isset($ticket_checkout['coupon_code']) && !empty($ticket_checkout['coupon_code']) &&
            $ticket_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-ticket') {
            $preferences['campaign_id'] = (int)$ticket_checkout['campaign_id'];
            $preferences['coupon_amount'] = ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                floor($ticket_checkout['discount'] * $currency_ratio) :
                floor($ticket_checkout['discount'] * $currency_ratio * 100) / 100;
            $preferences['coupon_code'] = strtoupper($ticket_checkout['coupon_code']);
        }

        // Set sponsor ID.
        $_test_user_v1 = get_option('_test_user_v1', false);
        if (!$_test_user_v1) {
            $preferences['sponsor_id'] = WC_WooMercadoPago_Module::get_sponsor_id();
        }

        // Debug/log this preference.
        $this->write_log(
            __FUNCTION__,
            'returning just created [$preferences] structure: ' .
            json_encode($preferences, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $preferences;
    }

    protected function create_url($order, $ticket_checkout)
    {
        // Creates the order parameters by checking the cart configuration.
        $preferences = $this->build_payment_preference($order, $ticket_checkout);
        // Checks for sandbox mode.
        $this->mp->sandbox_mode($this->sandbox);
        // Create order preferences with Mercado Pago API request.
        try {
            $checkout_info = $this->mp->create_payment(json_encode($preferences));
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

    /*
	 * ========================================================================
	 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
	 * ========================================================================
	 */

    // Enter a gateway method-specific rule within this function
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

    // Here, we process the status... this is the business rules!
    // Reference: https://www.mercadopago.com.br/developers/en/api-docs/basic-checkout/ipn/payment-status/


}