<?php
/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

class WC_WooMercadoPago_PreferencePSE extends WC_WooMercadoPago_PreferenceAbstract
{
    /**
     * WC_WooMercadoPago_PreferencePSE constructor.
     * @param $order
     * @param $pse_checkout
     */
    public function __construct($order, $pse_checkout)
    {
        parent::__construct($order, $pse_checkout);

        $this->preference['transaction_amount'] = $this->get_transaction_amount();
		$this->preference['description'] = implode(', ', $this->list_of_items);
        $this->preference['payment_method_id'] = $this->checkout['paymentMethodId'];
        $this->preference['payer']['email'] = $this->get_email();
		$this->preference['statement_descriptor'] = $this->payment->getOption('mp_statement_descriptor', 'Mercado Pago');
		$this->preference['additional_info']['items'] = $this->items;
        $this->preference['additional_info']['payer'] = $this->get_payer_custom();
		$this->preference['additional_info']['shipments'] = $this->shipments_receiver_address();
		if ($this->ship_cost > 0) {
            $this->preference['additional_info']['items'][] = $this->ship_cost_item();
		} 
		if ( 
            isset($this->checkout['discount']) && !empty($this->checkout['discount']) &&
            isset($this->checkout['coupon_code']) && !empty($this->checkout['coupon_code']) &&
            $this->checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-ticket'
        ) {
            $this->preference['additional_info']['items'][] = $this->add_discounts();
        }
        
        // PSE Fields
    if( $this->checkout['paymentMethodId'] == 'pse' ) {
        $this->preferences['additional_info']['ip_address'] = $this->get_ip();
        $this->preferences['payer']['identification'] = array(
          'type' => $this->checkout['docType'],
          'number' => $this->checkout['docNumber'],
        );
        $this->preferences['transaction_details'] = array(
          'financial_institution' => $this->checkout['bank']
        );
        $this->preferences['payer']['entity_type'] = $this->checkout['personType'];
      }
		
		$this->add_discounts_campaign();
    }

    /**
     * get_ip
     *
     * @return string
     */
    public function get_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

}