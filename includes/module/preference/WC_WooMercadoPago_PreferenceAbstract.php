<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */
abstract class WC_WooMercadoPago_PreferenceAbstract extends WC_Payment_Gateway
{
    protected $order;
    protected $checkout;
    protected $currency_ratio;
    protected $items;
    protected $order_total;
    protected $list_of_items;
    protected $preference;
    protected $selected_shipping;
    protected $ship_cost;
    protected $site_id;
    protected $site_data;
    protected $test_user_v1;

    /**
     * WC_WooMercadoPago_PreferenceAbstract constructor.
     * @param $order
     * @param null $checkout
     */
    public function __construct($order, $checkout = null)
    {
        $this->test_user_v1 = get_option('_test_user_v1', false);
        $this->site_id = get_option('_site_id_v1', '');
        $this->site_data = WC_WooMercadoPago_Configs::getCountryConfigs();
        $this->order = $order;
        $this->checkout = $checkout;
        $this->currency_ratio = $this->get_currency_conversion();
        $this->items = array();
        $this->order_total = 0;
        $this->list_of_items = array();
        $this->selected_shipping = $order->get_shipping_method();
        $this->ship_cost = $this->order->get_total_shipping() + $this->order->get_shipping_tax();
        $this->preference = $this->make_basic_preference();
        if (!$this->test_user_v1) {
            $this->preference['sponsor_id'] = $this->get_sponsor_id();
        }
        if (sizeof($this->order->get_items()) > 0) {
            $this->items = $this->get_items_build_array();
        }
    }

    /**
     * @return array
     */
    public function make_basic_preference()
    {
        $preference = array(
            'binary_mode' => $this->get_binary_mode(),
            'external_reference' => $this->get_external_reference(),
            'notification_url' => $this->get_notification_url(),
        );
        return $preference;
    }

    /**
     * @return int
     */
    public function get_currency_conversion()
    {
        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option('_mp_currency_conversion_v1', '');
        if (!empty($_mp_currency_conversion_v1)) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate($this->site_data[$this->site_id]['currency']);
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }
        return $currency_ratio;
    }


    /**
     * @return mixed
     */
    public function get_email()
    {
        if (method_exists($this->order, 'get_id')) {
            return $this->order->get_billing_email();
        } else {
            return $this->order->billing_email;
        }
    }

     /**
     * @return array
     */
    public function get_payer_custom()
    {
        $payer_additional_info = array(
            'first_name' => (method_exists($this->order, 'get_id') ? html_entity_decode($this->order->get_billing_first_name()) : html_entity_decode($this->order->billing_first_name)),
            'last_name' => (method_exists($this->order, 'get_id') ? html_entity_decode($this->order->get_billing_last_name()) : html_entity_decode($this->order->billing_last_name)),
            //'registration_date' =>
            'phone' => array(
                //'area_code' =>
                'number' => (method_exists($this->order, 'get_id') ? $this->order->get_billing_phone() : $this->order->billing_phone)
            ),
            'address' => array(
                'zip_code' => (method_exists($this->order, 'get_id') ? $this->order->get_billing_postcode() : $this->order->billing_postcode),
                //'street_number' =>
                'street_name' => html_entity_decode(
                    method_exists($this->order, 'get_id') ?
                        $this->order->get_billing_address_1() . ' / ' .
                        $this->order->get_billing_city() . ' ' .
                        $this->order->get_billing_state() . ' ' .
                        $this->order->get_billing_country() : $this->order->billing_address_1 . ' / ' .
                        $this->order->billing_city . ' ' .
                        $this->order->billing_state . ' ' .
                        $this->order->billing_country
                )
            )
        );
        return $payer_additional_info;
    }

    /**
     * @return array
     */
    public function get_items_build_array()
    {
        $gateway_discount = get_option('gateway_discount', 0);
        $items = array();
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
                array_push($items, array(
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
                    'unit_price' => ($this->site_data[$this->site_id]['currency'] == 'COP' || $this->site_data[$this->site_id]['currency'] == 'CLP') ?
                        floor(($line_amount - $discount_by_gateway) * $this->currency_ratio) : floor(($line_amount - $discount_by_gateway) * $this->currency_ratio * 100) / 100,
                    'currency_id' => $this->site_data[$this->site_id]['currency']
                ));
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public function ship_cost_item()
    {
        $item = array(
            'title' => method_exists($this->order, 'get_id') ? $this->order->get_shipping_method() : $this->order->shipping_method,
            'description' => __('Shipping service used by store', 'woocommerce-mercadopago'),
            'category_id' => get_option('_mp_category_name', 'others'),
            'quantity' => 1,
            'unit_price' => ($this->site_data[$this->site_id]['currency'] == 'COP' || $this->site_data[$this->site_id]['currency'] == 'CLP') ?
                floor($this->ship_cost * $this->currency_ratio) : floor($this->ship_cost * $this->currency_ratio * 100) / 100,
            'currency_id' => $this->site_data[$this->site_id]['currency']
        );
        return $item;
    }

    /**
     * @return array
     */
    public function shipments_receiver_address()
    {
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

    /**
     * @return mixed
     */
    public function get_notification_url()
    {
        if (!strrpos(get_site_url(), 'localhost')) {
            $notification_url = get_option('_mp_custom_domain', '');
            // Check if we have a custom URL.
            if (empty($notification_url) || filter_var($notification_url, FILTER_VALIDATE_URL) === FALSE) {
                return WC()->api_request_url(get_class($this));
            } else {
                return WC_Woo_Mercado_Pago_Module::fix_url_ampersand(esc_url(
                    $notification_url . '/wc-api/' . get_class($this) . '/'
                ));
            }
        }
    }

    /**
     * @return bool
     */
    public function get_binary_mode()
    {
        $binary_mode = get_option('binary_mode', 'no');
        if ($binary_mode == 'yes') {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     *  Get Sponsor Id
     */
    public function get_sponsor_id()
    {
        return WC_WooMercadoPago_Module::get_sponsor_id();
    }

    /**
     * @return string
     */
    public function get_external_reference()
    {
        $store_identificator = get_option('_mp_store_identificator', 'WC-');
        if (method_exists($this->order, 'get_id')) {
            return $store_identificator . $this->order->get_id();
        } else {
            return $store_identificator . $this->order->id;
        }
    }

    /**
     * @return array
     */
    public function get_preference()
    {
        return $this->preference;
    }

    /**
     * @return float|int
     */
    public function get_transaction_amount()
    {
        if ($this->site_data[$this->site_id]['currency'] == 'COP' || $this->site_data[$this->site_id]['currency'] == 'CLP') {
            return floor($this->order_total * $this->currency_ratio);
        } else {
            return floor($this->order_total * $this->currency_ratio * 100) / 100;
        }
    }

    /**
     * Discount Campaign
     */
    public function add_discounts_campaign()
    {
        if (
            isset($this->checkout['discount']) && !empty($this->checkout['discount']) &&
            isset($this->checkout['coupon_code']) && !empty($this->checkout['coupon_code']) &&
            $this->checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
        ) {
            $this->preference['campaign_id'] = (int)$this->checkout['campaign_id'];
            $this->preference['coupon_amount'] = ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                floor($this->checkout['discount'] * $this->currency_ratio) : floor($this->checkout['discount'] * $this->currency_ratio * 100) / 100;
            $this->preference['coupon_code'] = strtoupper($this->checkout['coupon_code']);
        }
    }
}