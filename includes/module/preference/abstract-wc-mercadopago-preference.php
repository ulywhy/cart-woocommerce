<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer 
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

abstract class MercadoPagoPreference  extends WC_Payment_Gateway {

    protected $order;
    protected $custom_checkout;
    protected $method_payment;
    protected $currency_ratio;
    protected $items;
    protected $order_total;
    protected $list_of_items;
    protected $preference;
    protected $selected_shipping;
    protected $ship_cost;

    public function __construct($order, $method_payment, $custom_checkout = null) {

        $this->site_id = get_option('_site_id_v1', '');
        $this->order = $order;
        $this->custom_checkout = $custom_checkout;
        $this->method_payment = $method_payment;
        $this->currency_ratio = 1;
        $this->items = array();
        $this->order_total = 0;
        $this->list_of_items = array();
        $this->selected_shipping = $order->get_shipping_method();
        $this->ship_cost = $this->order->get_total_shipping() + $this->order->get_shipping_tax();

        $this->preference = $this->make_preference();

        $this->get_currency_conversion();
        $this->get_items_build_array();
        $this->add_discounts();

    }

    public function make_preference() {
        $preference = array(
            'binary_mode' => $this->get_binary_mode(),
            'external_reference' => $this->get_external_reference(),
            'notification_url' => $this->get_notification_url(),
        );

        return $preference;
    }

    public function get_currency_conversion() {
        $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $this->currency_ratio = WC_Woo_Mercado_Pago_Module::get_conversion_rate($this->site_data['currency']);
            $this->currency_ratio = $this->currency_ratio > 0 ? $this->currency_ratio : 1;
        }
    }

    public function get_items_build_array() {
        $gateway_discount = get_option( 'gateway_discount', 0 );

        if (sizeof($this->order->get_items()) > 0) {
            foreach ($this->order->get_items() as $item) {
                if ($item['qty']) {
                    $product = new WC_product($item['product_id']);
                    $product_title = method_exists($product, 'get_description') ? $product->get_name() : $product->post->post_title;
                    $product_content = method_exists($product, 'get_description') ? $product->get_description() : $product->post->post_content;

                    // Calculates line amount and discounts.
                    $line_amount = $item['line_total'] + $item['line_tax'];
                    $discount_by_gateway = (float)$line_amount * ($gateway_discount / 100);
                    $this->order_total += ($line_amount - $discount_by_gateway);

                    // Add the item.
                    array_push($this->list_of_items, $product_title . ' x ' . $item['qty']);
                    array_push($this->items, array(
                        'id' => $item['product_id'],
                        'title' => html_entity_decode($product_title) . ' x ' . $item['qty'],
                        'description' => sanitize_file_name(html_entity_decode(
                            strlen($product_content) > 230 ?
                                substr($product_content, 0, 230) . '...' : $product_content
                        )),
                        'picture_url' => sizeof($this->order->get_items()) > 1 ?
                            plugins_url('assets/images/cart.png', plugin_dir_path(__FILE__)) : wp_get_attachment_url($product->get_image_id()),
                        'category_id' => get_option('_mp_category_name', 'others'),
                        'quantity' => 1,
                        'unit_price' => ($this->site_id == 'COP' || $this->site_id == 'CLP') ?
                            floor(($line_amount - $discount_by_gateway) * $this->currency_ratio) : floor(($line_amount - $discount_by_gateway) * $this->currency_ratio * 100) / 100,
                        'currency_id' => $this->site_id
                    ));
                }
            }
        }
    }

    // Creates the shipment cost structure.
    public function ship_cost() {
            $item = array(
                'title' => method_exists($this->order, 'get_id') ? $this->order->get_shipping_method() : $this->order->shipping_method,
                'title' => __('Shipping service used by store', 'woocommerce-mercadopago'),
                'description' => __('Shipping service used by store', 'woocommerce-mercadopago'),
                'category_id' => get_option('_mp_category_name', 'others'),
                'quantity' => 1,
                'unit_price' => ($this->site_id == 'COP' || $this->site_id == 'CLP') ?
                floor($this->ship_cost * $this->currency_ratio) : floor($this->ship_cost * $this->currency_ratio * 100) / 100,
                'currency_id' => $this->site_id
            );
            array_push($this->preference['items'], $item);
    }

    // Discounts features.
    // TODO Basic/Custom verificar a linha WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
    public function add_discounts() {
        if (
            isset($this->custom_checkout['discount']) && !empty($this->custom_checkout['discount']) &&
            isset($this->custom_checkout['coupon_code']) && !empty($this->ustom_checkout['coupon_code']) &&
            $this->custom_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
        ) {
            $item = array(
                'title' => __('Discount provided by store', 'woocommerce-mercadopago'),
                'description' => __('Discount provided by store', 'woocommerce-mercadopago'),
                'quantity' => 1,
                'category_id' => get_option('_mp_category_name', 'others'),
                'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                    -floor($this->custom_checkout['discount'] * $this->currency_ratio) : -floor($this->custom_checkout['discount'] * $this->currency_ratio * 100) / 100
            );
            $items[] = $item;
        }
    }

    // TODO Basic/Custom preference
    // Create the shipment address information set.
    public function shipments_receiver_address() {
        $shipments = array(
            'receiver_address' => array(
                'zip_code' => method_exists($this->order, 'get_id') ?
                    $this->order->get_shipping_postcode() : $this->order->shipping_postcode,
                //'street_number' =>
                'street_name' => html_entity_decode(
                    method_exists($this->order, 'get_id') ?
                        $this->order->get_shipping_address_1() . ' ' .
                        $this->order->get_shipping_address_2() . ' ' .
                        $this->order->get_shipping_city() . ' ' .
                        $this->order->get_shipping_state() . ' ' .
                        $this->order->get_shipping_country() : $this->order->shipping_address_1 . ' ' .
                        $this->order->shipping_address_2 . ' ' .
                        $this->order->shipping_city . ' ' .
                        $this->order->shipping_state . ' ' .
                        $this->order->shipping_country
                ),
                //'floor' =>
                'apartment' => method_exists($this->order, 'get_id') ?
                    $this->order->get_shipping_address_2() : $this->order->shipping_address_2
            )
        );

        return $shipments;
    }

    // TODO Basic/Custom preferences
    // Do not set IPN url if it is a localhost.
    public function get_notification_url() {
        if (!strrpos(get_site_url(), 'localhost')) {
            $notification_url = get_option('_mp_custom_domain', '');
            // Check if we have a custom URL.
            if (empty($notification_url) || filter_var($notification_url, FILTER_VALIDATE_URL) === FALSE) {
                $preferences['notification_url'] = WC()->api_request_url('WC_WooMercadoPago_CustomGateway');
            } else {
                $preferences['notification_url'] = WC_Woo_Mercado_Pago_Module::fix_url_ampersand(esc_url(
                    $notification_url . '/wc-api/WC_WooMercadoPago_CustomGateway/'
                ));
            }
        }
    }

    // TODO Basic/Custom preferences
    // Binary Mode
    public function get_binary_mode() {
        $binary_mode = get_option( 'binary_mode', 'no' );
        if ( $binary_mode == 'yes' ) {
            return true;
        } else {
            return false;
        }
    }

    // TODO Basic/Custom preferences
    // Set sponsor ID.
    public function get_sponsor_id() {
        $_test_user_v1 = get_option('_test_user_v1', false);
        if (!$_test_user_v1) {
            $preferences['sponsor_id'] = WC_Woo_Mercado_Pago_Module::get_sponsor_id();
        }
    }

    // TODO Basic/Custom preferences
    public function get_external_reference() {
        $store_identificator =  get_option('_mp_store_identificator', 'WC-');
        if (method_exists($this->order, 'get_id')) {
            return $store_identificator . $this->order->get_id();
        } else {
            return $store_identificator .  $this->order->id;
        }
    }
    
    public function get_preference() {
        return $this->preference;
    }

}
