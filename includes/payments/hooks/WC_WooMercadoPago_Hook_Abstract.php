<?php
/**
 * Class WC_WooMercadoPago_Hook_Abstract
 */

abstract class WC_WooMercadoPago_Hook_Abstract
{
    public $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }


    public function loadHooks()
    {
        add_action('woocommerce_order_action_cancel_order', array($this, 'process_cancel_order_meta_box_actions'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->payment->id, array($this, 'custom_process_admin_options'));
        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_ipn_response'));
        add_action('woocommerce_cart_calculate_fees', array($this, 'add_discount'), 10);
        add_filter('woocommerce_gateway_title', array($this, 'get_payment_method_title'), 10, 2);
        add_action('send_options_payment_gateways' . strtolower(get_class($this)), array($this, 'send_settings_mp'));

        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script'));
            add_action('woocommerce_thankyou', array($this, 'update_mp_settings_script'));
        }
    }


    public function add_checkout_scripts()
    {
        if (is_checkout() && $this->is_available()) {
            if (!get_query_var('order-received')) {
                wp_enqueue_style(
                    'woocommerce-mercadopago-style',
                    plugins_url('assets/css/custom_checkout_mercadopago.css', plugin_dir_path(__FILE__))
                );
                wp_enqueue_script('woocommerce-mercadopago-pse-js', 'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js');
            }
        }
    }

    public function get_payment_method_title($title)
    {
        if (!is_checkout() && !(defined('DOING_AJAX') && DOING_AJAX)) {
            return $title;
        }
        if ($title != $this->title || $this->gateway_discount == 0) {
            return $title;
        }
        if (!is_numeric($this->gateway_discount) || $this->gateway_discount < -99 || $this->gateway_discount > 99) {
            return $title;
        }
        $total = (float)WC()->cart->subtotal;
        $price_percent = $this->gateway_discount / 100;
        if ($price_percent > 0) {
            $title .= ' (' . __('Discount of', 'woocommerce-mercadopago') . ' ' . strip_tags(wc_price($total * $price_percent)) . ')';
        } elseif ($price_percent < 0) {
            $title .= ' (' . __('Fee of', 'woocommerce-mercadopago') . ' ' . strip_tags(wc_price(-$total * $price_percent)) . ')';
        }
        return $title;
    }

    /**
     *
     */
    public function add_mp_settings_script()
    {
        $public_key = get_option('_mp_public_key');
        $is_test_user = get_option('_test_user_v1', false);

        if (!empty($public_key) && !$is_test_user) {

            $w = WC_WooMercadoPago_Module::woocommerce_instance();
            $available_payments = array();
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            foreach ($gateways as $g) {
                $available_payments[] = $g->id;
            }
            $available_payments = str_replace('-', '_', implode(', ', $available_payments));
            if (wp_get_current_user()->ID != 0) {
                $logged_user_email = wp_get_current_user()->user_email;
            } else {
                $logged_user_email = null;
            }
            ?>
            <script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
            <script type="text/javascript">
                try {
                    var MA = ModuleAnalytics;
                    MA.setPublicKey('<?php echo $public_key; ?>');
                    MA.setPlatform('WooCommerce');
                    MA.setPlatformVersion('<?php echo $w->version; ?>');
                    MA.setModuleVersion('<?php echo WC_WooMercadoPago_Module::VERSION; ?>');
                    MA.setPayerEmail('<?php echo($logged_user_email != null ? $logged_user_email : ""); ?>');
                    MA.setUserLogged( <?php echo(empty($logged_user_email) ? 0 : 1); ?> );
                    MA.setInstalledModules('<?php echo $available_payments; ?>');
                    MA.post();
                } catch (err) {
                }
            </script>
            <?php
        }
    }

    /**
     * @param $order_id
     */
    public function update_mp_settings_script($order_id)
    {
        $_mp_public_key = get_option( '_mp_public_key' );
        $is_test_user = get_option( '_test_user_v1', false );
        if ( ! empty( $_mp_public_key ) && ! $is_test_user ) {
            if ( get_post_meta( $order_id, '_used_gateway', true ) != 'WC_WooMercadoPago_BasicGateway' ) {
                return;
            }
            $this->payment->log->write_log( __FUNCTION__, 'updating order of ID ' . $order_id );
            echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				try {
					var MA = ModuleAnalytics;
                    MA.setPublicKey('. $_mp_public_key .');
					MA.setPaymentType("basic");
					MA.setCheckoutType("basic");
					MA.put();
				} catch(err) {}
			</script>';
        }
    }

}