<?php

class WC_WooMercadoPago_Hook_Basic extends WC_WooMercadoPago_Hook_Abstract
{
    public function __construct($payment)
    {
        parent::__construct($payment);
    }

    public function loadHooks($is_instance = false)
    {
        parent::loadHooks();

        if (!empty($this->payment->settings['enabled']) && $this->payment->settings['enabled'] == 'yes') {
            add_action('woocommerce_after_checkout_form', array($this, 'add_mp_settings_script_basic'));
            add_action('woocommerce_thankyou', array($this, 'update_mp_settings_script_basic'));
        }

        add_action('woocommerce_receipt_' . $this->payment->id,
            function ($order) {
                echo $this->render_order_form($order);
            }
        );

        add_action(
            'wp_head',
            function () {
                if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '>=')) {
                    $page_id = wc_get_page_id('checkout');
                } else {
                    $page_id = woocommerce_get_page_id('checkout');
                }
                if (is_page($page_id)) {
                    echo '<style type="text/css">#MP-Checkout-dialog { z-index: 9999 !important; }</style>' . PHP_EOL;
                }
            }
        );
    }

    /**
     * @param $order_id
     * @return string
     */
    public function render_order_form($order_id)
    {
        $order = wc_get_order($order_id);
        $url = $this->payment->create_url($order);

        $banner_url = get_option('_mp_custom_banner');
        if (!isset($banner_url) || empty($banner_url)) {
            $banner_url = $this->payment->site_data['checkout_banner'];
        }

        if ('modal' == $this->payment->method && $url) {
            $this->payment->log->write_log(__FUNCTION__, 'rendering Mercado Pago lightbox (modal window).');

            // ===== The checkout is made by displaying a modal to the customer =====
            $html = '<style type="text/css">
						#MP-Checkout-dialog #MP-Checkout-IFrame { bottom: -28px !important; height: 590px !important; }
					</style>';
            $html .= '<script type="text/javascript" src="https://secure.mlstatic.com/mptools/render.js"></script>
					<script type="text/javascript">
						(function() { $MPC.openCheckout({ url: "' . esc_url($url) . '", mode: "modal" }); })();
					</script>';
            $html .= '<img width="468" height="60" src="' . $banner_url . '">';
            $html .= '<p></p><p>' . wordwrap(
                    __('Thank you for your order. Please, proceed with your payment clicking in the bellow button.', 'woocommerce-mercadopago'),
                    60, '<br>'
                ) . '</p>
					<a id="submit-payment" href="' . esc_url($url) . '" name="MP-Checkout" class="button alt" mp-mode="modal">' .
                __('Pay with Mercado Pago', 'woocommerce-mercadopago') .
                '</a> <a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">' .
                __('Cancel order &amp; Clear cart', 'woocommerce-mercadopago') .
                '</a>';
            return $html;
            // ===== The checkout is made by displaying a modal to the customer =====

        } else {

            $this->payment->log->write_log(__FUNCTION__, 'unable to build Mercado Pago checkout URL.');

            // ===== Reaching at this point means that the URL could not be build by some reason =====
            $html = '<p>' .
                __('An error occurred when proccessing your payment. Please try again or contact us for assistence.', 'woocommerce-mercadopago') .
                '</p>' .
                '<a class="button" href="' . esc_url($order->get_checkout_payment_url()) . '">' .
                __('Click to try again', 'woocommerce-mercadopago') .
                '</a>
			';
            return $html;
            // ===== Reaching at this point means that the URL could not be build by some reason =====

        }

    }

    /**
     * Scripts to basic
     */
    public function add_mp_settings_script_basic()
    {
        parent::add_mp_settings_script();
    }

    /**
     *
     */
    public function update_mp_settings_script_basic($order_id)
    {
        parent::update_mp_settings_script($order_id);
    }


}