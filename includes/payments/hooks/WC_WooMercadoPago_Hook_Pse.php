<?php

/**
 * Class WC_WooMercadoPago_Hook_Pse
 */
class WC_WooMercadoPago_Hook_Pse extends WC_WooMercadoPago_Hook_Abstract
{
    /**
     * WC_WooMercadoPago_Hook_Pse constructor.
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
        add_action('wp_enqueue_scripts', array($this, 'add_checkout_scripts'));
        //add_action('valid_mercadopago_pse_ipn_request', array($this, 'successful_request'));
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_discount_pse'), 10);
        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_pse'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'success_page_pse'));
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
    public function success_page_pse($order_id)
    {
        parent::update_mp_settings_script($order_id);
        $order = wc_get_order($order_id);
        $transaction_details = (method_exists($order, 'get_meta')) ? $order->get_meta('_transaction_details_pse') : get_post_meta($order->id, '_transaction_details_pse', true);
        if (empty($transaction_details)) {
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
     * Add Discount PSE
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
            $this->payment->log->write_log(__FUNCTION__, 'pse checkout trying to apply discount...');
            $value = ($this->payment->site_data['currency'] == 'COP' || $this->payment->site_data['currency'] == 'CLP') ?
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

    /**
     * @return mixed
     */
    public function custom_process_admin_options()
    {
        $this->payment->init_settings();
        $post_data = $this->payment->get_post_data();
        foreach ( $this->payment->get_form_fields() as $key => $field ) {
            if ( 'title' !== $this->payment->get_field_type( $field ) ) {
                $value = $this->payment->get_field_value( $key, $field, $post_data );
                if ( $key == 'gateway_discount') {
                    if ( ! is_numeric( $value ) || empty ( $value ) || $value < -99 || $value > 99 ) {
                        $this->payment->settings[$key] = 0;
                    } else {
                        $this->payment->settings[$key] = $value;
                    }
                } else {
                    $this->payment->settings[$key] = $this->payment->get_field_value( $key, $field, $post_data );
                }
            }
        }
        $_site_id_v1 = get_option( '_site_id_v1', '' );
        $is_test_user = get_option( '_test_user_v1', false );
        if ( ! empty( $_site_id_v1 ) && ! $is_test_user ) {
            // Analytics.
            $infra_data = WC_WooMercadoPago_Module::get_common_settings();
            $infra_data['checkout_custom_pse'] = ( $this->payment->settings['enabled'] == 'yes' ? 'true' : 'false' );
            $this->mpInstance->analytics_save_settings( $infra_data );
        }
        return update_option($this->payment->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->payment->id, $this->payment->settings ));
    }
}