<?php

class WC_WooMercadoPago_Hook_Custom extends WC_WooMercadoPago_Hook_Abstract
{
    /**
     * WC_WooMercadoPago_Hook_Custom constructor.
     * @param $payment
     */
    public function __construct($payment)
    {
        parent::__construct($payment);
    }

    /**
     * Load Hooks
     */
    public function loadHooks()
    {
        parent::loadHooks();
        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('wp_enqueue_scripts', array($this, 'add_checkout_scripts_custom'));
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_custom'));
            add_action('woocommerce_thankyou', array($this, 'update_mp_settings_script_custom'));
        }
    }

    /**
     *  Add Discount
     */
    public function add_discount()
    {
        parent::add_discount_abst();
        return;
    }

    /**
     * @return bool
     * @throws WC_WooMercadoPago_Exception
     */
    public function custom_process_admin_options()
    {
        $updateOptions = parent::custom_process_admin_options();
        return $updateOptions;
    }

    /**
     * Add Checkout Scripts
     */
    public function add_checkout_scripts_custom()
    {
        if (is_checkout() && $this->payment->is_available() && !get_query_var('order-received')) {
            wp_enqueue_script('mercado-pago-module-custom-js', 'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js');
            wp_enqueue_script('woocommerce-mercadopago-checkout', plugins_url('../../assets/js/credit-card.js', plugin_dir_path(__FILE__)), array('jquery'), null, true);

            wp_localize_script(
                'woocommerce-mercadopago-checkout',
                'wc_mercadopago_params',
                array(
                    'site_id'     => $this->payment->getOption('_site_id_v1'),
                    'public_key'  => $this->payment->getPublicKey(),
                    'payer_email' => $this->payment->logged_user_email,
                    'apply'       => __('Apply', 'woocommerce-mercadopago'),
                    'remove'      => __('Remove', 'woocommerce-mercadopago'),
                    'choose'      => __('To choose', 'woocommerce-mercadopago'),
                    'other_bank'  => __('Other bank', 'woocommerce-mercadopago'),
                    'loading'     => plugins_url('../../assets/images/', plugin_dir_path(__FILE__)) . 'loading.gif',
                    'check'       => plugins_url('../../assets/images/', plugin_dir_path(__FILE__)) . 'check.png',
                    'error'       => plugins_url('../../assets/images/', plugin_dir_path(__FILE__)) . 'error.png'
                )
            );
        }
    }

    /**
     *
     */
    public function add_mp_settings_script_custom()
    {
        parent::add_mp_settings_script();
    }

    /**
     * @param $order_id
     */
    public function update_mp_settings_script_custom($order_id)
    {
        echo parent::update_mp_settings_script($order_id);
    }
}
