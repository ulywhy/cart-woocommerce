<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 *
 * WC_WooMercadoPago_CustomGateway
 * 
 */
class WC_WooMercadoPago_CustomGateway extends WC_WooMercadoPago_Payments {

    /**
	* Constructor.
	*/
    public function __construct( $is_instance = false ) {

        $this->id                 = 'woo-mercado-pago-custom';

        $this->supports           = array( 'products', 'refunds' );
        $this->method_title       = __( 'Mercado Pago - Custom Checkout', 'woocommerce-mercadopago' );
        $this->method_description = '<img width="200" height="52" src="' .
            plugins_url( 'assets/images/mplogo.png', plugin_dir_path( __FILE__ ) ) . '"><br><br><strong>' .
            __( 'We give you the possibility to adapt the payment experience you want to offer 100% in your website, mobile app or anywhere you want. You can build the design that best fits your business model, aiming to maximize conversion.', 'woocommerce-mercadopago' ) .
            '</strong>';

        // Mercao Pago instance.
        $this->mp_requerimente();

        $this->title              = $this->get_option( 'title', __( 'Mercado Pago - Custom Checkout', 'woocommerce-mercadopago' ) );
        $this->description        = $this->get_option( 'description' );
        $this->icon               = apply_filters('woocommerce_mercadopago_icon', plugins_url( 'assets/images/mercadopago.png', plugin_dir_path( __FILE__ ) ));

        // Payment Options: How payment options behaves
        $this->coupon_mode        = $this->get_option( 'coupon_mode', 'no' );
        $this->binary_mode        = $this->get_option( 'binary_mode', 'no' );
        $this->gateway_discount   = $this->get_option( 'gateway_discount', 0 );

        $this->logging_debug();

        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->admin_notices();

        $this->mp_hooks();

        $this->payment_type     = "credit_card";
        $this->checkout_type    = "custom";

    }

    // Define field of Settings paymet method woocomerce page able payments
    public function mp_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'woocommerce-mercadopago' ),
                'type' => 'checkbox',
                'label' => __( 'Enable', 'woocommerce-mercadopago' ) . ' ' . $this->title ,
                'default' => 'no'
            ),
            'checkout_options_title' => array(
                'title' => __( 'Checkout Interface: How checkout is shown', 'woocommerce-mercadopago' ),
                'type' => 'title'
            ),
            'title' => array(
                'title' => __( 'Title', 'woocommerce-mercadopago' ),
                'type' => 'text',
                'description' => __( 'Title shown to the client in the checkout.', 'woocommerce-mercadopago' ),
                'default' => __( 'Mercado Pago - Generic Checkout', 'woocommerce-mercadopago' )
            ),
            'description' => array(
                'title' => __( 'Description', 'woocommerce-mercadopago' ),
                'type' => 'textarea',
                'description' => __( 'Description shown to the client in the checkout.', 'woocommerce-mercadopago' ),
                'default' => __( 'Pay with Mercado Pago Generic checkout', 'woocommerce-mercadopago' )
            ),
            'payment_title' => array(
                'title' => __( 'Payment Options: How payment options behaves', 'woocommerce-mercadopago' ),
                'type' => 'title'
            ),
            'coupon_mode' => array(
                'title' => __( 'Coupons', 'woocommerce-mercadopago' ),
                'type' => 'checkbox',
                'label' => __( 'Enable coupons of discounts', 'woocommerce-mercadopago' ),
                'default' => 'no',
                'description' => __( 'If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'woocommerce-mercadopago' )
            ),
            'binary_mode' => array(
                'title' => __( 'Binary Mode', 'woocommerce-mercadopago' ),
                'type' => 'checkbox',
                'label' => __( 'Enable binary mode for checkout status', 'woocommerce-mercadopago' ),
                'default' => 'no',
                'description' => __( 'When charging a credit card, only [approved] or [reject] status will be taken.', 'woocommerce-mercadopago' )
            ),
            'gateway_discount' => array(
                'title' => __( 'Discount/Fee by Gateway', 'woocommerce-mercadopago' ),
                'type' => 'number',
                'description' => __( 'Give a percentual (-99 to 99) discount or fee for your customers if they use this payment gateway. Use negative for fees, positive for discounts.', 'woocommerce-mercadopago' ),
                'default' => '0',
                'custom_attributes' => array(
                    'step' 	=> '0.01',
                    'min'	=> '-99',
                    'max' => '99'
                ) 
            )
        ); 

        return $this->form_fields;
    }

    /**
     *
	 * ========================================================================
	 * SAVE CHECKOUT SETTINGS 
	 * ========================================================================
	 *
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the
	 * erroring field out.
	 * @return bool was anything saved?
	 */

    public function process_settings($post_data) {
        foreach ( $this->get_form_fields() as $key => $field ) {
            if ( 'title' !== $this->get_field_type( $field ) ) {
                $value = $this->get_field_value( $key, $field, $post_data );
                if ( $key == 'gateway_discount') {
                    if ( ! is_numeric( $value ) || empty ( $value ) ) {
                        $this->settings[$key] = 0;
                    } else {
                        if ( $value < -99 || $value > 99 || empty ( $value ) ) {
                            $this->settings[$key] = 0;
                        } else {
                            $this->settings[$key] = $value;
                        }
                    }
                } else {
                    $this->settings[$key] = $this->get_field_value( $key, $field, $post_data );
                }
            }
        } 
    }

    public function define_settings_to_send() {
        $infra_data = WC_Woo_Mercado_Pago_Module::get_common_settings();
        $infra_data['checkout_custom_credit_card'] = ( $this->settings['enabled'] == 'yes' ? 'true' : 'false' );
        $infra_data['checkout_custom_credit_card_coupon'] = ( $this->settings['coupon_mode'] == 'yes' ? 'true' : 'false' );
        return $infra_data;
    }

    /*
     * ========================================================================
     * HANDLES ORDER
     * ========================================================================
	 */

    public function get_order_status( $status_detail ) {
        switch ( $status_detail ) {
            case 'accredited':
                return __( 'Done, your payment was accredited!', 'woocommerce-mercadopago' );
            case 'pending_contingency':
                return __( 'We are processing the payment. In less than an hour we will e-mail you the results.', 'woocommerce-mercadopago' );
            case 'pending_review_manual':
                return __( 'We are processing the payment. In less than 2 business days we will tell you by e-mail whether it has accredited or we need more information.', 'woocommerce-mercadopago' );
            case 'cc_rejected_bad_filled_card_number':
                return __( 'Check the card number.', 'woocommerce-mercadopago' );
            case 'cc_rejected_bad_filled_date':
                return __( 'Check the expiration date.', 'woocommerce-mercadopago' );
            case 'cc_rejected_bad_filled_other':
                return __( 'Check the information.', 'woocommerce-mercadopago' );
            case 'cc_rejected_bad_filled_security_code':
                return __( 'Check the security code.', 'woocommerce-mercadopago' );
            case 'cc_rejected_blacklist':
                return __( 'We could not process your payment.', 'woocommerce-mercadopago' );
            case 'cc_rejected_call_for_authorize':
                return __( 'You must authorize the payment of your orders.', 'woocommerce-mercadopago' );
            case 'cc_rejected_card_disabled':
                return __( 'Call your card issuer to activate your card. The phone is on the back of your card.', 'woocommerce-mercadopago' );
            case 'cc_rejected_card_error':
                return __( 'We could not process your payment.', 'woocommerce-mercadopago' );
            case 'cc_rejected_duplicated_payment':
                return __( 'You already made a payment for that amount. If you need to repay, use another card or other payment method.', 'woocommerce-mercadopago' );
            case 'cc_rejected_high_risk':
                return __( 'Your payment was rejected. Choose another payment method. We recommend cash.', 'woocommerce-mercadopago' );
            case 'cc_rejected_insufficient_amount':
                return __( 'Your payment do not have sufficient funds.', 'woocommerce-mercadopago' );
            case 'cc_rejected_invalid_installments':
                return __( 'Your payment does not process payments with selected installments.', 'woocommerce-mercadopago' );
            case 'cc_rejected_max_attempts':
                return __( 'You have reached the limit of allowed attempts. Choose another card or another payment method.', 'woocommerce-mercadopago' );
            case 'cc_rejected_other_reason':
                return __( 'This payment method did not process the payment.', 'woocommerce-mercadopago' );
            default:
                return __( 'This payment method did not process the payment.', 'woocommerce-mercadopago' );
        }
    }

    /*
	 * ========================================================================
	 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
	 * ========================================================================
	 */

    public function payment_fields() {
        wp_enqueue_script( 'wc-credit-card-form' );

        $amount = $this->get_order_total();
        $logged_user_email = ( wp_get_current_user()->ID != 0 ) ? wp_get_current_user()->user_email : null;
        $customer = isset( $logged_user_email ) ? $this->mp->get_or_create_customer( $logged_user_email ) : null;
        $discount_action_url = get_site_url() . '/index.php/woocommerce-mercadopago/?wc-api=' . get_class($this);

        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option( '_mp_currency_conversion_v1', '' );
        if ( ! empty( $_mp_currency_conversion_v1 ) ) {
            $currency_ratio = WC_Woo_Mercado_Pago_Module::get_conversion_rate( $this->site_data['currency'] );
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }

        $banner_url = get_option( '_mp_custom_banner' );
        if ( ! isset( $banner_url ) || empty( $banner_url ) ) {
            $banner_url = $this->site_data['checkout_banner_custom'];
        }

        $parameters = array(
            'amount'                 => $amount,
            // ===
            'site_id'                => get_option( '_site_id_v1' ),
            'public_key'             => get_option( '_mp_public_key' ),
            'coupon_mode'            => isset( $logged_user_email ) ? $this->coupon_mode : 'no',
            'discount_action_url'    => $discount_action_url,
            'payer_email'            => $logged_user_email,
            // ===
            'images_path'            => plugins_url( 'assets/images/', plugin_dir_path( __FILE__ ) ),
            'banner_path'            => $banner_url,
            'customer_cards'         => isset( $customer ) ? ( isset( $customer['cards'] ) ? $customer['cards'] : array() ) : array(),
            'customerId'             => isset( $customer ) ? ( isset( $customer['id'] ) ? $customer['id'] : null ) : null,
            'currency_ratio'         => $currency_ratio,
            'woocommerce_currency'   => get_woocommerce_currency(),
            'account_currency'       => $this->site_data['currency'],
            // ===
            'path_to_javascript'     => plugins_url( 'assets/js/credit-card.js', plugin_dir_path( __FILE__ ) )
        );
        wc_get_template(
            'credit-card/payment-form.php',
            $parameters,
            'woo/mercado/pago/module/',
            WC_Woo_Mercado_Pago_Module::get_templates_path()
        );
    }

    /**
     * ========================================================================
	 * PROCESS PAYMENT.
	 * ========================================================================
     *  
	 * Summary: Handle the payment and processing the order.
	 * Description: This function is called after we click on [place_order] button, and each field is
	 * passed to this function through $_POST variable.
	 * @return an array containing the result of the processment and the URL to redirect.
	 */
    public function process_payment( $order_id ) {
        if ( ! isset( $_POST['mercadopago_custom'] ) ) {
            return;
        }
        $custom_checkout = $_POST['mercadopago_custom'];
        $order = wc_get_order( $order_id );
        if ( method_exists( $order, 'update_meta_data' ) ) {
            $order->update_meta_data( '_used_gateway', get_class($this) );
            $order->save();
        } else {
            update_post_meta( $order_id, '_used_gateway', get_class($this) );
        }
        // Mexico country case.
        if ( ! isset( $custom_checkout['paymentMethodId'] ) || empty( $custom_checkout['paymentMethodId'] ) ) {
            $custom_checkout['paymentMethodId'] = $custom_checkout['paymentMethodSelector'];
        }
        if ( isset( $custom_checkout['amount'] ) && ! empty( $custom_checkout['amount'] ) &&
            isset( $custom_checkout['token'] ) && ! empty( $custom_checkout['token'] ) &&
            isset( $custom_checkout['paymentMethodId'] ) && ! empty( $custom_checkout['paymentMethodId'] ) &&
            isset( $custom_checkout['installments'] ) && ! empty( $custom_checkout['installments'] ) &&
            $custom_checkout['installments'] != -1 ) {
            $response = $this->create_url( $order, $custom_checkout );

            // Check for card save.
            if ( method_exists( $order, 'update_meta_data' ) ) {
                if ( isset( $custom_checkout['doNotSaveCard'] ) ) {
                    $order->update_meta_data( '_save_card', 'no' );
                } else {
                    $order->update_meta_data( '_save_card', 'yes' );
                }
                $order->save();
            } else {
                if ( isset( $custom_checkout['doNotSaveCard'] ) ) {
                    update_post_meta( $order_id, '_save_card', 'no' );
                } else {
                    update_post_meta( $order_id, '_save_card', 'yes' );
                }
            }
            // Switch on response.
            if ( array_key_exists( 'status', $response ) ) {
                switch ( $response['status'] ) {
                    case 'approved':
                        WC()->cart->empty_cart();
                        wc_add_notice(
                            '<p>' . $this->get_order_status( 'accredited' ) . '</p>',
                            'notice'
                        );
                        $order->add_order_note(
                            'Mercado Pago: ' . __( 'Payment approved.', 'woocommerce-mercadopago' )
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'pending':
                        // Order approved/pending, we just redirect to the thankyou page.
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                        break;
                    case 'in_process':
                        // For pending, we don't know if the purchase will be made, so we must inform this status.
                        WC()->cart->empty_cart();
                        wc_add_notice(
                            '<p>' . $this->get_order_status( $response['status_detail'] ) . '</p>' .
                            '<p><a class="button" href="' . esc_url( $order->get_checkout_order_received_url() ) . '">' .
                            __( 'Check your order resume', 'woocommerce-mercadopago' ) .
                            '</a></p>',
                            'notice'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url( true )
                        );
                        break;
                    case 'rejected':
                        // If rejected is received, the order will not proceed until another payment try, so we must inform this status.
                        wc_add_notice(
                            '<p>' . __( 'Your payment was refused. You can try again.', 'woocommerce-mercadopago' ) . '<br>' .
                            $this->get_order_status( $response['status_detail'] ) .
                            '</p>' .
                            '<p><a class="button" href="' . esc_url( $order->get_checkout_payment_url() ) . '">' .
                            __( 'Click to try again', 'woocommerce-mercadopago' ) .
                            '</a></p>',
                            'error'
                        );
                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_payment_url( true )
                        );
                        break;
                    case 'cancelled':
                    case 'in_mediation':
                    case 'charged-back':
                        // If we enter here (an order generating a direct [cancelled, in_mediation, or charged-back] status),
                        // them there must be something very wrong!
                        break;
                    default:
                        break;
                }
            } else {
                // Process when fields are imcomplete.
                wc_add_notice(
                    '<p>' .
                    __( 'A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form?', 'woocommerce-mercadopago' ) . ' MERCADO PAGO: ' .
                    WC_Woo_Mercado_Pago_Module::get_common_error_messages( $response ) .
                    '</p>',
                    'error'
                );
                return array(
                    'result' => 'fail',
                    'redirect' => '',
                );
            }
        } else {
            // Process when fields are imcomplete.
            wc_add_notice(
                '<p>' .
                __( 'A problem was occurred when processing your payment. Please, try again.', 'woocommerce-mercadopago' ) .
                '</p>',
                'error'
            );
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }

    /**
	 * Summary: Build Mercado Pago preference.
	 * Description: Create Mercado Pago preference and get init_point URL based in the order options
	 * from the cart.
	 * @return the preference object.
	 */
    private function build_payment_preference( $order, $custom_checkout ) {
        // A string to register items (workaround to deal with API problem that shows only first item).
        $items = array();
        $order_total = 0;
        $list_of_items = array();
        // Find currency rate.
        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option( '_mp_currency_conversion_v1', '' );
        if ( ! empty( $_mp_currency_conversion_v1 ) ) {
            $currency_ratio = WC_Woo_Mercado_Pago_Module::get_conversion_rate( $this->site_data['currency'] );
            $currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
        }
        // Here we build the array that contains ordered items, from customer cart.
        if ( sizeof( $order->get_items() ) > 0 ) {
            foreach ( $order->get_items() as $item ) {
                if ( $item['qty'] ) {
                    $product = new WC_product( $item['product_id'] );
                    $product_title = method_exists( $product, 'get_description' ) ?
                        $product->get_name() :
                    $product->post->post_title;
                    $product_content = method_exists( $product, 'get_description' ) ?
                        $product->get_description() :
                    $product->post->post_content;
                    // Calculates line amount and discounts.
                    $line_amount = $item['line_total'] + $item['line_tax'];
                    $discount_by_gateway = (float) $line_amount * ( $this->gateway_discount / 100 );
                    $order_total += ($line_amount - $discount_by_gateway);
                    // Add the item.
                    array_push( $list_of_items, $product_title . ' x ' . $item['qty'] );
                    array_push( $items, array(
                        'id' => $item['product_id'],
                        'title' => html_entity_decode( $product_title ) . ' x ' . $item['qty'],
                        'description' => sanitize_file_name( html_entity_decode(
                            strlen( $product_content ) > 230 ?
                            substr( $product_content, 0, 230 ) . '...' :
                            $product_content
                        ) ),
                        'picture_url' => sizeof( $order->get_items() ) > 1 ?
                        plugins_url( 'assets/images/cart.png', plugin_dir_path( __FILE__ ) ) :
                        wp_get_attachment_url( $product->get_image_id() ),
                        'category_id' => get_option( '_mp_category_name', 'others' ),
                        'quantity' => 1,
                        'unit_price' => ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
                        floor( ( $line_amount - $discount_by_gateway ) * $currency_ratio ) :
                        floor( ( $line_amount - $discount_by_gateway ) * $currency_ratio * 100 ) / 100
                    ) );
                }
            }
        }
        // Creates the shipment cost structure.
        $ship_cost = ($order->get_total_shipping() + $order->get_shipping_tax());
        if ( $ship_cost > 0 ) {
            $order_total += $ship_cost;
            $item = array(
                'title' => method_exists( $order, 'get_id' ) ?
                $order->get_shipping_method() :
                $order->shipping_method,
                'description' => __( 'Shipping service used by store', 'woocommerce-mercadopago' ),
                'category_id' => get_option( '_mp_category_name', 'others' ),
                'quantity' => 1,
                'unit_price' => ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
                floor( $ship_cost * $currency_ratio ) :
                floor( $ship_cost * $currency_ratio * 100 ) / 100
            );
            $items[] = $item;
        }
        // Discounts features.
        if ( isset( $custom_checkout['discount'] ) && ! empty( $custom_checkout['discount'] ) &&
            isset( $custom_checkout['coupon_code'] ) && ! empty( $custom_checkout['coupon_code'] ) &&
            $custom_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom' ) {
            $item = array(
                'title' => __( 'Discount provided by store', 'woocommerce-mercadopago' ),
                'description' => __( 'Discount provided by store', 'woocommerce-mercadopago' ),
                'quantity' => 1,
                'category_id' => get_option( '_mp_category_name', 'others' ),
                'unit_price' => ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
                -floor( $custom_checkout['discount'] * $currency_ratio ) :
                -floor( $custom_checkout['discount'] * $currency_ratio * 100 ) / 100
            );
            $items[] = $item;
        }
        // Build additional information from the customer data.
        $payer_additional_info = array(
            'first_name' => ( method_exists( $order, 'get_id' ) ?
                             html_entity_decode( $order->get_billing_first_name() ) :
                             html_entity_decode( $order->billing_first_name ) ),
            'last_name' => ( method_exists( $order, 'get_id' ) ?
                            html_entity_decode( $order->get_billing_last_name() ) :
                            html_entity_decode( $order->billing_last_name ) ),
            //'registration_date' =>
            'phone' => array(
                //'area_code' =>
                'number' => ( method_exists( $order, 'get_id' ) ?
                             $order->get_billing_phone() :
                             $order->billing_phone )
            ),
            'address' => array(
                'zip_code' => ( method_exists( $order, 'get_id' ) ?
                               $order->get_billing_postcode() :
                               $order->billing_postcode
                              ),
                //'street_number' =>
                'street_name' => html_entity_decode( method_exists( $order, 'get_id' ) ?
                                                    $order->get_billing_address_1() . ' / ' .
                                                    $order->get_billing_city() . ' ' .
                                                    $order->get_billing_state() . ' ' .
                                                    $order->get_billing_country() :
                                                    $order->billing_address_1 . ' / ' .
                                                    $order->billing_city . ' ' .
                                                    $order->billing_state . ' ' .
                                                    $order->billing_country
                                                   )
            )
        );
        // Create the shipment address information set.
        $shipments = array(
            'receiver_address' => array(
                'zip_code' => method_exists( $order, 'get_id' ) ?
                $order->get_shipping_postcode() :
                $order->shipping_postcode,
                //'street_number' =>
                'street_name' => html_entity_decode( method_exists( $order, 'get_id' ) ?
                                                    $order->get_shipping_address_1() . ' ' .
                                                    $order->get_shipping_address_2() . ' ' .
                                                    $order->get_shipping_city() . ' ' .
                                                    $order->get_shipping_state() . ' ' .
                                                    $order->get_shipping_country() :
                                                    $order->shipping_address_1 . ' ' .
                                                    $order->shipping_address_2 . ' ' .
                                                    $order->shipping_city . ' ' .
                                                    $order->shipping_state . ' ' .
                                                    $order->shipping_country
                                                   ),
                //'floor' =>
                'apartment' => method_exists( $order, 'get_id' ) ?
                $order->get_shipping_address_2() :
                $order->shipping_address_2
            )
        );
        // The payment preference.
        $preferences = array(
            'transaction_amount' => ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
            floor( $order_total * $currency_ratio ) :
            floor( $order_total * $currency_ratio * 100 ) / 100,
            'token' => $custom_checkout['token'],
            'description' => implode( ', ', $list_of_items ),
            'installments' => (int) $custom_checkout['installments'],
            'payment_method_id' => $custom_checkout['paymentMethodId'],
            'payer' => array(
                'email' => method_exists( $order, 'get_id' ) ?
                $order->get_billing_email() :
                $order->billing_email
            ),
            'external_reference' => get_option( '_mp_store_identificator', 'WC-' ) .
            ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id ),
            'statement_descriptor' => get_option( '_mp_statement_descriptor', 'Mercado Pago' ),
            'binary_mode' => ( $this->binary_mode == 'yes' ),
            'additional_info' => array(
                'items' => $items,
                'payer' => $payer_additional_info,
                'shipments' => $shipments
            )
        );
        // Customer's Card Feature, add only if it has issuer id.
        if ( array_key_exists( 'token', $custom_checkout ) ) {
            $preferences['metadata']['token'] = $custom_checkout['token'];
            if ( array_key_exists( 'issuer', $custom_checkout ) ) {
                if ( ! empty( $custom_checkout['issuer'] ) ) {
                    $preferences['issuer_id'] = (integer) $custom_checkout['issuer'];
                }
            }
            if ( ! empty( $custom_checkout['CustomerId'] ) ) {
                $preferences['payer']['id'] = $custom_checkout['CustomerId'];
            }
        }
        // Do not set IPN url if it is a localhost.
        if ( ! strrpos( get_site_url(), 'localhost' ) ) {
            $notification_url = get_option( '_mp_custom_domain', '' );
            // Check if we have a custom URL.
            if ( empty( $notification_url ) || filter_var( $notification_url, FILTER_VALIDATE_URL ) === FALSE ) {
                $preferences['notification_url'] = WC()->api_request_url( 'WC_WooMercadoPago_CustomGateway' );
            } else {
                $preferences['notification_url'] = WC_Woo_Mercado_Pago_Module::fix_url_ampersand( esc_url(
                    $notification_url . '/wc-api/WC_WooMercadoPago_CustomGateway/'
                ) );
            }
        }
        // Discounts features.
        if ( isset( $custom_checkout['discount'] ) && ! empty( $custom_checkout['discount'] ) &&
            isset( $custom_checkout['coupon_code'] ) && ! empty( $custom_checkout['coupon_code'] ) &&
            $custom_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-custom' ) {
            $preferences['campaign_id'] = (int) $custom_checkout['campaign_id'];
            $preferences['coupon_amount'] = ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
                floor( $custom_checkout['discount'] * $currency_ratio ) :
            floor( $custom_checkout['discount'] * $currency_ratio * 100 ) / 100;
            $preferences['coupon_code'] = strtoupper( $custom_checkout['coupon_code'] );
        }
        // Set sponsor ID.
        $_test_user_v1 = get_option( '_test_user_v1', false );
        if ( ! $_test_user_v1 ) {
            $preferences['sponsor_id'] = WC_Woo_Mercado_Pago_Module::get_sponsor_id();
        }
        // Debug/log this preference.
        $this->write_log(
            __FUNCTION__,
            'returning just created [$preferences] structure: ' .
            json_encode( $preferences, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
        );
        return $preferences;
    }

    protected function create_url( $order, $custom_checkout ) {
        // Creates the order parameters by checking the cart configuration.
        $preferences = $this->build_payment_preference( $order, $custom_checkout );
        // Checks for sandbox mode.
        $this->mp->sandbox_mode( $this->sandbox );
        // Create order preferences with Mercado Pago API request.
        try {
            $checkout_info = $this->mp->post( '/v1/payments', json_encode( $preferences) );
            if ( $checkout_info['status'] < 200 || $checkout_info['status'] >= 300 ) {
                // Mercado Pago throwed an error.
                $this->write_log(
                    __FUNCTION__,
                    'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']
                );
                return $checkout_info['response']['message'];
            } elseif ( is_wp_error( $checkout_info ) ) {
                // WordPress throwed an error.
                $this->write_log(
                    __FUNCTION__,
                    'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']
                );
                return $checkout_info['response']['message'];
            } else {
                // Obtain the URL.
                $this->write_log(
                    __FUNCTION__,
                    'payment link generated with success from mercado pago, with structure as follow: ' .
                    json_encode( $checkout_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
                );
                // TODO: Verify sandbox availability.
                //if ( 'yes' == $this->sandbox ) {
                //	return $checkout_info['response']['sandbox_init_point'];
                //} else {
                return $checkout_info['response'];
                //}
            }
        } catch ( MercadoPagoException $ex ) {
            // Something went wrong with the payment creation.
            $this->write_log(
                __FUNCTION__,
                'payment creation failed with exception: ' .
                json_encode( $ex, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
            );
            return $ex->getMessage();
        }
    }

    /**
	 * Summary: Check if we have existing customer card, if not we create and save it.
	 * Description: Check if we have existing customer card, if not we create and save it.
	 * @return boolean true/false depending on the validation result.
	 */
    public function check_and_save_customer_card( $checkout_info ) {
        $this->write_log(
            __FUNCTION__,
            'checking info to create card: ' .
            json_encode( $checkout_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
        );
        $custId = null;
        $token = null;
        $issuer_id = null;
        $payment_method_id = null;
        if ( isset( $checkout_info['payer']['id'] ) && ! empty( $checkout_info['payer']['id'] ) ) {
            $custId = $checkout_info['payer']['id'];
        } else {
            return;
        }
        if ( isset( $checkout_info['metadata']['token'] ) && ! empty( $checkout_info['metadata']['token'] ) ) {
            $token = $checkout_info['metadata']['token'];
        } else {
            return;
        }
        if ( isset( $checkout_info['issuer_id'] ) && ! empty( $checkout_info['issuer_id'] ) ) {
            $issuer_id = (integer) ( $checkout_info['issuer_id'] );
        }
        if ( isset( $checkout_info['payment_method_id'] ) && ! empty( $checkout_info['payment_method_id'] ) ) {
            $payment_method_id = $checkout_info['payment_method_id'];
        }
        try {
            $this->mp->create_card_in_customer( $custId, $token, $payment_method_id, $issuer_id );
        } catch ( MercadoPagoException $ex ) {
            $this->write_log(
                __FUNCTION__,
                'card creation failed: ' .
                json_encode( $ex, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
            );
        }
    }

    /*
	 * ========================================================================
	 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
	 * ========================================================================
	 */

    // Enter a gateway method-specific rule within this function
    public function mp_config_rule_is_available() {
        $_mp_access_token   = get_option( '_mp_access_token' );
        $_mp_debug_mode     = get_option( '_mp_debug_mode', '' );
        $is_prod_credentials = strpos( $_mp_access_token, 'TEST' ) === false;

        // If we do not have SSL in production environment, we are not allowed to process.
        if ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' ) {
            if ( empty ( $_mp_debug_mode ) ) {
                return false;
            }
        } elseif ( ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' ) && $is_prod_credentials ) {
            // If we don't have SSL, we can only enable this payment method with TEST credentials.
            return false;
        } else {
            return true;
        }
    }

    /*
	 * ========================================================================
	 * IPN MECHANICS (SERVER SIDE)
	 * ========================================================================
	 */

    /**
	 * Summary: This call checks any incoming notifications from Mercado Pago server.
	 * Description: This call checks any incoming notifications from Mercado Pago server.
	 */
    public function check_ipn_response() {
        @ob_clean();
        $this->write_log(
            __FUNCTION__,
            'received _get content: ' .
            json_encode( $_GET, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
        );
        // Setup sandbox mode.
        $this->mp->sandbox_mode( $this->sandbox );
        // Over here, $_GET should come with this JSON structure:
        // {
        // 	"topic": <string>,
        // 	"id": <string>
        // }
        // If not, the IPN is corrupted in some way.
        $data = $_GET;
        if ( isset( $data['coupon_id'] ) && ! empty( $data['coupon_id'] ) ) {
            // Process coupon evaluations.
            if ( isset( $data['payer'] ) && ! empty( $data['payer'] ) ) {
                $response = $this->mp->check_discount_campaigns( $data['amount'], $data['payer'], $data['coupon_id'] );
                header( 'HTTP/1.1 200 OK' );
                header( 'Content-Type: application/json' );
                echo json_encode( $response );
            } else {
                $obj = new stdClass();
                $obj->status = 404;
                $obj->response = array(
                    'message' => __( 'Please, inform your email in billing address to use this feature', 'woocommerce-mercadopago' ),
                    'error' => 'payer_not_found',
                    'status' => 404,
                    'cause' => array()
                );
                header( 'HTTP/1.1 200 OK' );
                header( 'Content-Type: application/json' );
                echo json_encode( $obj );
            }
            exit( 0 );
        } else if ( ! isset( $data['data_id'] ) || ! isset( $data['type'] ) ) {
            // Received IPN call from v0.
            $this->write_log(
                __FUNCTION__,
                'data_id or type not set: ' .
                json_encode( $data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
            );
            if ( ! isset( $data['id'] ) || ! isset( $data['topic'] ) ) {
                $this->write_log(
                    __FUNCTION__,
                    'Mercado Pago Request failure: ' .
                    json_encode( $data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
                );
                wp_die( __( 'Mercado Pago Request Failure', 'woocommerce-mercadopago' ) );
            } else {
                // At least, check if its a v0 ipn.
                header( 'HTTP/1.1 200 OK' );
            }
        } else {
            // Needed informations are present, so start process then.
            try {
                if ( $data['type'] == 'payment' ) {
                    $access_token = array( 'access_token' => $this->mp->get_access_token() );
                    $payment_info = $this->mp->get( '/v1/payments/' . $data['data_id'], $access_token, false );
                    if ( ! is_wp_error( $payment_info ) && ( $payment_info['status'] == 200 || $payment_info['status'] == 201 ) ) {
                        if ( $payment_info['response'] ) {
                            header( 'HTTP/1.1 200 OK' );
                            do_action( 'valid_mercadopago_ipn_request', $payment_info['response'] );
                        }
                    } else {
                        $this->write_log(
                            __FUNCTION__,
                            'error when processing received data: ' .
                            json_encode( $payment_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
                        );
                    }
                }
            } catch ( MercadoPagoException $ex ) {
                $this->write_log(
                    __FUNCTION__,
                    'MercadoPagoException: ' .
                    json_encode( $ex, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
                );
            }
        }
    }

    // Here, we process the status... this is the business rules!
    // Reference: https://www.mercadopago.com.br/developers/en/api-docs/basic-checkout/ipn/payment-status/
    public function process_status_mp_business($data, $order) {  

        $status = isset( $data['status'] ) ? $data['status'] : 'pending';
        $total_paid = isset( $data['transaction_details']['total_paid_amount'] ) ? $data['transaction_details']['total_paid_amount'] : 0.00;
        $total_refund = isset( $data['transaction_amount_refunded'] ) ? $data['transaction_amount_refunded'] : 0.00;
        // WooCommerce 3.0 or later.
        if ( method_exists( $order, 'update_meta_data' ) ) {
            // Updates the type of gateway.
            $order->update_meta_data( '_used_gateway', get_class($this) );
            if ( ! empty( $data['payer']['email'] ) ) {
                $order->update_meta_data( __( 'Payer email', 'woocommerce-mercadopago' ), $data['payer']['email'] );
            }
            if ( ! empty( $data['payment_type_id'] ) ) {
                $order->update_meta_data( __( 'Payment type', 'woocommerce-mercadopago' ), $data['payment_type_id'] );
            }
            $order->update_meta_data(
                'Mercado Pago - Payment ' . $data['id'],
                '[Date ' . date( 'Y-m-d H:i:s', strtotime( $data['date_created'] ) ) .
                ']/[Amount ' . $data['transaction_amount'] .
                ']/[Paid ' . $total_paid .
                ']/[Refund ' . $total_refund . ']'
            );
            $order->update_meta_data( '_Mercado_Pago_Payment_IDs', $data['id'] );
            $order->save();
        } else {
            // Updates the type of gateway.
            update_post_meta( $order_id, '_used_gateway', 'WC_WooMercadoPago_CustomGateway' );
            if ( ! empty( $data['payer']['email'] ) ) {
                update_post_meta( $order_id, __( 'Payer email', 'woocommerce-mercadopago' ), $data['payer']['email'] );
            }
            if ( ! empty( $data['payment_type_id'] ) ) {
                update_post_meta( $order_id, __( 'Payment type', 'woocommerce-mercadopago' ), $data['payment_type_id'] );
            }
            update_post_meta(
                $order_id,
                'Mercado Pago - Payment ' . $data['id'],
                '[Date ' . date( 'Y-m-d H:i:s', strtotime( $data['date_created'] ) ) .
                ']/[Amount ' . $data['transaction_amount'] .
                ']/[Paid ' . $total_paid .
                ']/[Refund ' . $total_refund . ']'
            );
            update_post_meta( $order_id, '_Mercado_Pago_Payment_IDs', $data['id'] );
        }
        return $status;
    }

    public function mp_rule_approved($data, $order) {
        $order->add_order_note( 'Mercado Pago: ' . __( 'Payment approved.', 'woocommerce-mercadopago' ) );

        // Check if we can save the customer card.
        $save_card = ( method_exists( $order, 'get_meta' ) ) ?
            $order->get_meta( '_save_card' ) :
        get_post_meta( $order->id, '_save_card', true );
        if ( $save_card === 'yes' ) {
            $this->write_log( __FUNCTION__, 'Saving customer card: ' . json_encode( $data['card'], JSON_PRETTY_PRINT ) );
            $this->check_and_save_customer_card( $data );
        }
        $order->payment_complete();
        $order->update_status( WC_Woo_Mercado_Pago_Module::get_wc_status_for_mp_status( 'approved' ));
    }

}

new WC_WooMercadoPago_CustomGateway( true );
