<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer 
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

class MercadoPagoPreferenceCustom extends MercadoPagoPreference {

    public function __construct($order, $custom_checkout = null, $method_payment) {
        parent::__construct($order, $custom_checkout = null, $method_payment);

        $this->preferences['transaction_amount'] = $this->get_transaction_amount();
        $this->preferences['token'] = $this->custom_checkout['token'];
        $this->preferences['description'] = implode( ', ', $this->list_of_items );
        $this->preferences['installments'] = (int) $this->custom_checkout['installments'];
        $this->preferences['payment_method_id'] = $this->custom_checkout['paymentMethodId'];
        $this->preferences['payer']['email'] = $this->get_email();
        if ( ! empty( $this->custom_checkout['CustomerId'] ) ) {
		    $this->preferences['payer']['id'] = $this->custom_checkout['CustomerId'];
	    }
        $this->preferences['statement_descriptor'] = get_option( '_mp_statement_descriptor', 'Mercado Pago' );
        $this->preferences['additional_info']['items'] = $this->items;
        $this->preferences['additional_info']['payer'] = $this->get_payer_custom;
        $this->preferences['additional_info']['shipments'] = $this->shipments_receiver_address();
    
        if ( isset( $this->checkout_info['metadata']['token'] ) && ! empty( $this->checkout_info['metadata']['token'] ) ) {
			$this->preferences['metadata']['token']  = $this->checkout_info['metadata']['token'];
		}

        $this->add_discounts_campaign();
        
    }

    // TODO Custom preferences
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
    // TODO Basic/Custom verificar a linha WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
    public function add_discounts_campaign() {
        if (
            isset($this->custom_checkout['discount']) && !empty($this->custom_checkout['discount']) &&
            isset($this->custom_checkout['coupon_code']) && !empty($this->custom_checkout['coupon_code']) &&
            $this->custom_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom'
        ) {
            $this->preferences['campaign_id'] = (int)$this->custom_checkout['campaign_id'];
            $this->preferences['coupon_amount'] = ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                floor($this->custom_checkout['discount'] * $this->currency_ratio) : floor($this->custom_checkout['discount'] * $this->currency_ratio * 100) / 100;
            $this->preferences['coupon_code'] = strtoupper($this->custom_checkout['coupon_code']);
        }
    }

    // TODO Custom preferences
    // Customer's Card Feature, add only if it has issuer id.
    public function customer_card() {
        if (array_key_exists('token', $this->custom_checkout)) {
            $preferences['metadata']['token'] = $this->custom_checkout['token'];
            if (array_key_exists('issuer', $this->custom_checkout)) {
                if (!empty($this->custom_checkout['issuer'])) {
                    $preferences['issuer_id'] = (integer)$this->custom_checkout['issuer'];
                }
            }
            if (!empty($this->custom_checkout['CustomerId'])) {
                $preferences['payer']['id'] = $this->custom_checkout['CustomerId'];
            }
        }
    }
    
    public function get_transaction_amount() {
        if( $this->site_id['currency'] == 'COP' || $this->site_id['currency'] == 'CLP' ) {
            return floor( $this->order_total * $this->currency_ratio );
        } else {
            return floor( $this->order_total * $this->currency_ratio * 100 ) / 100;

        }
    }
    
    public function get_email() {
        if (method_exists( $this->order, 'get_id' )) {
            return $this->order->get_billing_email();
        } else {
            return $this->order->billing_email;
        }
    }
}