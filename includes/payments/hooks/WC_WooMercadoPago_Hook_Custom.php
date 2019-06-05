<?php

class WC_WooMercadoPago_Hook_Custom extends WC_WooMercadoPago_Hook_Abstract
{
    public function __construct($payment)
    {
        parent::__construct($payment);
    }


    public function loadHooks()
    {
        parent::loadHooks();
        add_action('wp_enqueue_scripts', array($this, 'add_checkout_scripts'));

        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_custom'));
            add_action('woocommerce_thankyou', array($this, 'update_mp_settings_script_custom'));
        }
    }

    public function add_mp_settings_script_custom() {
        parent::add_mp_settings_script();
    }

    public function update_mp_settings_script_custom($order_id)
    {
        parent::update_mp_settings_script($order_id);
    }
}