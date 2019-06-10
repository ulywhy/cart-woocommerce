<?php

/**
 * Class WC_WooMercadoPago_Hook_Ticket
 */
class WC_WooMercadoPago_Hook_Ticket extends WC_WooMercadoPago_Hook_Abstract
{
    /**
     * WC_WooMercadoPago_Hook_Ticket constructor.
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
        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_ticket'));
            add_action('woocommerce_thankyou_' . $this->payment->id, array($this, 'update_mp_settings_script_ticket'));
        }
    }

    /**
     *  Add Discount
     */
    public function add_discount()
    {
        if (!isset($_POST['mercadopago_ticket'])) {
            return;
        }
        if (is_admin() && !defined('DOING_AJAX') || is_cart()) {
            return;
        }
        $ticket_checkout = $_POST['mercadopago_ticket'];
        parent::add_discount_abst($ticket_checkout);
    }

    /**
     * @return mixed
     */
    public function custom_process_admin_options()
    {
        $this->payment->init_settings();
        $post_data = $this->payment->get_post_data();
        foreach ($this->payment->get_form_fields() as $key => $field) {
            if ('title' !== $this->payment->get_field_type($field)) {
                $value = $this->payment->get_field_value($key, $field, $post_data);
                if ($key == 'gateway_discount') {
                    if (!is_numeric($value) || empty ($value)) {
                        $this->payment->settings[$key] = 0;
                    } else {
                        if ($value < -99 || $value > 99 || empty ($value)) {
                            $this->payment->settings[$key] = 0;
                        } else {
                            $this->payment->settings[$key] = $value;
                        }
                    }
                } elseif ($key == 'date_expiration') {
                    if (!is_numeric($value) || empty ($value)) {
                        $this->payment->settings[$key] = 3;
                    } else {
                        if ($value < 1 || $value > 30 || empty ($value)) {
                            $this->payment->settings[$key] = 3;
                        } else {
                            $this->payment->settings[$key] = $value;
                        }
                    }
                } else {
                    $this->payment->settings[$key] = $this->payment->get_field_value($key, $field, $post_data);
                }
            }
        }
        $_site_id_v1 = get_option('_site_id_v1', '');
        $is_test_user = get_option('_test_user_v1', false);
        if (!empty($_site_id_v1) && !$is_test_user) {
            // Create MP instance.
            $mp = new MP(WC_WooMercadoPago_Module::get_module_version(), get_option('_mp_access_token'));
            $email = (wp_get_current_user()->ID != 0) ? wp_get_current_user()->user_email : null;
            $mp->set_email($email);
            $locale = get_locale();
            $locale = (strpos($locale, '_') !== false && strlen($locale) == 5) ? explode('_', $locale) : array('', '');
            $mp->set_locale($locale[1]);
            // Analytics.
            $infra_data = WC_WooMercadoPago_Module::get_common_settings();
            $infra_data['checkout_custom_ticket'] = ($this->payment->settings['enabled'] == 'yes' ? 'true' : 'false');
            $infra_data['checkout_custom_ticket_coupon'] = ($this->payment->settings['coupon_mode'] == 'yes' ? 'true' : 'false');
            $response = $mp->analytics_save_settings($infra_data);
        }
        // Apply updates.
        return update_option($this->payment->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->payment->id, $this->payment->settings));
    }

    /**
     * MP Settings Ticket
     */
    public function add_mp_settings_script_ticket()
    {
        parent::add_mp_settings_script();
    }

    /**
     * @param $order_id
     */
    public function update_mp_settings_script_ticket($order_id)
    {
        parent::update_mp_settings_script($order_id);
        $order = wc_get_order($order_id);
        $transaction_details = (method_exists($order, 'get_meta')) ? $order->get_meta('_transaction_details_ticket') : get_post_meta($order->id, '_transaction_details_ticket', true);

        if (empty($transaction_details)) {
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
}