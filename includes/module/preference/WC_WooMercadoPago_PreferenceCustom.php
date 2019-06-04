<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer 
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

require_once dirname(__FILE__) . '/WC_WooMercadoPago_PreferenceAbstractphp';

class WC_WooMercadoPago_PreferenceCustom extends WC_WooMercadoPago_PreferenceAbstract {
    public function __construct($order, $custom_checkout) {
        parent::__construct($order, $custom_checkout);
        $this->preference['transaction_amount'] = $this->get_transaction_amount();
        $this->preference['token'] = $this->checkout['token'];
        $this->preference['description'] = implode(', ', $this->list_of_items);
        $this->preference['installments'] = (int)$this->checkout['installments'];
        $this->preference['payment_method_id'] = $this->checkout['paymentMethodId'];
        $this->preference['payer']['email'] = $this->get_email();
        if (array_key_exists('token', $this->checkout)) {
            $this->preference['metadata']['token'] = $this->checkout['token'];
            if (!empty($this->checkout['CustomerId'])) {
                $this->preference['payer']['id'] = $this->checkout['CustomerId'];
            }
            if (!empty($this->checkout['issuer'])) {
                $this->preference['issuer_id'] = (integer)$this->checkout['issuer'];
            }
        }
        $this->preference['statement_descriptor'] = get_option('_mp_statement_descriptor', 'Mercado Pago');
        $this->preference['additional_info']['items'] = $this->items;
        $this->preference['additional_info']['payer'] = $this->get_payer_custom();
        $this->preference['additional_info']['shipments'] = $this->shipments_receiver_address();
        if (
            isset($this->checkout['discount']) && !empty($this->checkout['discount']) &&
            isset($this->checkout['coupon_code']) && !empty($this->ustom_checkout['coupon_code']) &&
            $this->checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
        ) {
            $this->preference['additional_info']['items'][] = $this->add_discounts();
        }
        $this->add_discounts_campaign();
    }
    // Build additional information from the customer data.
    public function get_payer_custom() {
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
    // Discounts features.
    public function add_discounts() {
        $item = array(
            'title' => __('Discount provided by store', 'woocommerce-mercadopago'),
            'description' => __('Discount provided by store', 'woocommerce-mercadopago'),
            'quantity' => 1,
            'category_id' => get_option('_mp_category_name', 'others'),
            'unit_price' => ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                -floor($this->checkout['discount'] * $this->currency_ratio) : -floor($this->checkout['discount'] * $this->currency_ratio * 100) / 100
        );
        return $item;
    }
    // Discounts features.
    public function add_discounts_campaign() {
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
    public function get_transaction_amount() {
        if ($this->site_id == 'COP' || $this->site_id == 'CLP') {
            return floor($this->order_total * $this->currency_ratio);
        } else {
            return floor($this->order_total * $this->currency_ratio * 100) / 100;
        }
    }
    public function get_email() {
        if (method_exists($this->order, 'get_id')) {
            return $this->order->get_billing_email();
        } else {
            return $this->order->billing_email;
        }
    }
}