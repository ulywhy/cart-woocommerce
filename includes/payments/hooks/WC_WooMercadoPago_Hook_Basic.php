<?php

/**
 * Class WC_WooMercadoPago_Hook_Basic
 */
class WC_WooMercadoPago_Hook_Basic extends WC_WooMercadoPago_Hook_Abstract
{
    /**
     * WC_WooMercadoPago_Hook_Basic constructor.
     * @param $payment
     */
    public function __construct($payment)
    {
        parent::__construct($payment);
    }

    /**
     * @param bool $is_instance
     */
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

    public function custom_process_admin_options() {
        $this->payment->init_settings();
        $post_data = $this->payment->get_post_data();
        foreach ( $this->payment->get_form_fields() as $key => $field ) {
            if ( 'title' !== $this->payment->get_field_type( $field ) ) {
                $value = $this->payment->get_field_value( $key, $field, $post_data );
                if ( $key == 'two_cards_mode' ) {
                    // We dont save two card mode as it should come from api.
                    unset( $this->payment->settings[$key] );
                    $this->payment->two_cards_mode = ( $value == 'yes' ? 'active' : 'inactive' );
                }
                elseif ( $key == 'gateway_discount') {
                    if ( ! is_numeric( $value ) || empty ( $value ) ) {
                        $this->payment->settings[$key] = 0;
                    } else {
                        if ( $value < -99 || $value > 99 || empty ( $value ) ) {
                            $this->payment->settings[$key] = 0;
                        } else {
                            $this->payment->settings[$key] = $value;
                        }
                    }
                } else {
                    $this->payment->settings[$key] = $this->payment->get_field_value( $key, $field, $post_data );
                }
            }
        }
        $_site_id_v1 = get_option( '_site_id_v1', '' );
        $is_test_user = get_option( '_test_user_v1', false );
        if ( ! empty( $_site_id_v1 ) ) {
            // Create MP instance.
            $mp = new MP(
                WC_WooMercadoPago_Module::get_module_version(),
                get_option( '_mp_access_token' )
            );
            $email = ( wp_get_current_user()->ID != 0 ) ? wp_get_current_user()->user_email : null;
            $mp->set_email( $email );
            $locale = get_locale();
            $locale = ( strpos( $locale, '_' ) !== false && strlen( $locale ) == 5 ) ? explode( '_', $locale ) : array('','');
            $mp->set_locale( $locale[1] );
            // Analytics.
            if ( ! $is_test_user ) {
                $infra_data = WC_WooMercadoPago_Module::get_common_settings();
                $infra_data['checkout_basic'] = ( $this->payment->settings['enabled'] == 'yes' ? 'true' : 'false' );
                $infra_data['two_cards'] = ( $this->payment->two_cards_mode == 'active' ? 'true' : 'false' );
                $response = $mp->analytics_save_settings( $infra_data );
            }
            // Two cards mode.
            $response = $mp->set_two_cards_mode( $this->payment->two_cards_mode );
        }
        // Apply updates.
        return update_option(
            $this->payment->get_option_key(),
            apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->payment->id, $this->payment->settings )
        );
    }

    public function process_settings($post_data)
    {
        foreach ($this->payment->get_form_fields() as $key => $field) {
            if ('title' !== $this->payment->get_field_type($field)) {
                $value = $this->payment->get_field_value($key, $field, $post_data);
                if ($key == 'two_cards_mode') {
                    unset($this->payment->settings[$key]);
                    $this->payment->two_cards_mode = ($value == 'yes' ? 'active' : 'inactive');
                } elseif ($key == 'gateway_discount') {
                    if (!is_numeric($value) || empty ($value)) {
                        $this->payment->settings[$key] = 0;
                    } else {
                        if ($value < -99 || $value > 99 || empty ($value)) {
                            $this->payment->settings[$key] = 0;
                        } else {
                            $this->payment->settings[$key] = $value;
                        }
                    }
                } else {
                    $this->payment->settings[$key] = $this->payment->get_field_value($key, $field, $post_data);
                }
            }
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

    /**
     *  Discount not apply
     */
    public function add_discount(){
        return;
    }

}