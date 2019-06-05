<?php

class WC_WooMercadoPago_Hook_Pse extends WC_WooMercadoPago_Hook_Abstract
{
    public function __construct($payment)
    {
        parent::__construct($payment);
    }

    /**
     *
     */
    public function loadHooks()
    {
        parent::loadHooks();
        add_action('wp_enqueue_scripts', array($this, 'add_checkout_scripts'));
        //add_action('valid_mercadopago_pse_ipn_request', array($this, 'successful_request'));
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_discount_pse'), 10);
        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_pse'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'update_mp_settings_script_pse'));
        }
    }

    /**
     *
     */
    public function add_mp_settings_script_pse()
    {
        parent::add_mp_settings_script();
    }

    /**
     * @param $order_id
     */
    public function update_mp_settings_script_pse($order_id)
    {
        parent::update_mp_settings_script($order_id);
        $order = wc_get_order($order_id);
        $used_gateway = (method_exists($order, 'get_meta')) ?
            $order->get_meta('_used_gateway') :
            get_post_meta($order->id, '_used_gateway', true);
        $transaction_details = (method_exists($order, 'get_meta')) ?
            $order->get_meta('_transaction_details_pse') :
            get_post_meta($order->id, '_transaction_details_pse', true);

        // A watchdog to prevent operations from other gateways.
        if ($used_gateway != 'WC_WooMercadoPago_PSEGateway' || empty($transaction_details)) {
            return;
        }

        $html = '<p>' .
            __('Thank you for your order. Please, transfer the money to get your order approved.', 'woocommerce-mercadopago') .
            '</p>' .
            '<p><iframe src="' . $transaction_details . '" style="width:100%; height:1000px;"></iframe></p>' .
            '<a id="submit-payment" target="_blank" href="' . $transaction_details . '" class="button alt"' .
            ' style="font-size:1.25rem; width:75%; height:48px; line-height:24px; text-align:center;">' .
            __('Transfer', 'woocommerce-mercadopago') .
            '</a> ';
        $added_text = '<p>' . $html . '</p>';
        echo $added_text;
    }

    /**
     *
     */
    public function add_discount_pse()
    {
        if (!isset($_POST['mercadopago_pse'])) {
            return;
        }

        if (is_admin() && !defined('DOING_AJAX') || is_cart()) {
            return;
        }

        $pse_checkout = $_POST['mercadopago_pse'];
        if (isset($pse_checkout['discount']) && !empty($pse_checkout['discount']) &&
            isset($pse_checkout['coupon_code']) && !empty($pse_checkout['coupon_code']) &&
            $pse_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-pse') {

            $this->write_log(__FUNCTION__, 'pse checkout trying to apply discount...');

            $value = ($this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP') ?
                floor($pse_checkout['discount'] / $pse_checkout['currency_ratio']) :
                floor($pse_checkout['discount'] / $pse_checkout['currency_ratio'] * 100) / 100;
            global $woocommerce;
            if (apply_filters('wc_mercadopagopse_module_apply_discount', 0 < $value, $woocommerce->cart)) {
                $woocommerce->cart->add_fee(sprintf(__('Discount for %s coupon', 'woocommerce-mercadopago'), esc_attr($pse_checkout['campaign'])),
                    ($value * -1), false
                );
            }
        }

    }



}