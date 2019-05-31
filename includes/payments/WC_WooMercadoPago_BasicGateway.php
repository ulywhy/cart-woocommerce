<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 *
 * WC_WooMercadoPago_BasicGateway
 * 
 */
class WC_WooMercadoPago_BasicGateway extends WC_WooMercadoPago_PaymentAbstract {

    /**
	* Constructor.
	*/
    public function __construct()
    {
        $this->id = 'woo-mercado-pago-basic';
        $this->method_title = __( 'Mercado Pago - Basic Checkout', 'woocommerce-mercadopago' );
        $this->title = $this->get_option( 'title', __( 'Mercado Pago - Basic Checkout', 'woocommerce-mercadopago' ) );
        $this->method = $this->get_option( 'method', 'redirect' );
        $this->auto_return = $this->get_option( 'auto_return', 'yes' );
        $this->success_url = $this->get_option( 'success_url', '' );
        $this->failure_url = $this->get_option( 'failure_url', '' );
        $this->pending_url = $this->get_option( 'pending_url', '' );
        $this->installments = $this->get_option( 'installments', '24' );
        $this->two_cards_mode = 'inactive';
        $this->ex_payments = $this->getExPayments();





    }

    /**
     * @return array
     */
    private function getExPayments(){
        $ex_payments = array();
        $get_ex_payment_options = get_option('_all_payment_methods_v0', '');
        if( ! empty($get_ex_payment_options) ) {
            foreach($get_ex_payment_options = explode(',', $get_ex_payment_options) as $get_ex_payment_option) {
                if ($this->get_option( 'ex_payments_' . $get_ex_payment_option, 'yes') == 'no') {
                    $ex_payments[] = $get_ex_payment_option;
                }
            }
        }
        return $ex_payments;
    }


    // Display the discount in payment method title.
    public function get_payment_method_title_basic( $title, $id ) {
        if ( ! is_checkout() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return $title;
        }
        if ( $title != $this->title || $this->gateway_discount == 0 ) {
            return $title;
        }
        if ( WC()->session->chosen_payment_method === 'woo-mercado-pago-subscription' ) {
            return $title;
        }
        if ( ! is_numeric( $this->gateway_discount ) || $this->gateway_discount < -99 || $this->gateway_discount > 99 ) {
            return $title;
        }
        $total = (float) WC()->cart->subtotal;
        $price_percent = $this->gateway_discount / 100;
        if ( $price_percent > 0 ) {
            $title .= ' (' . __( 'Discount of', 'woocommerce-mercadopago' ) . ' ' . strip_tags( wc_price( $total * $price_percent ) ) . ')';
        } elseif ( $price_percent < 0 ) {
            $title .= ' (' . __( 'Fee of', 'woocommerce-mercadopago' ) . ' ' . strip_tags( wc_price( -$total * $price_percent ) ) . ')';
        }
        return $title;
    }

    // Define field of Settings paymet method woocomerce page able payments
    public function mp_form_fields() {
        $this->two_cards_mode = $this->mp->check_two_cards();
        $this->form_fields['enabled'] = $this->field_enabled();
        $this->form_fields['checkout_options_title'] = $this->field_checkout_options_title();
        $this->form_fields['title'] = $this->field_title();
        $this->form_fields['description'] = $this->field_description();        
        $this->form_fields['method'] = $this->field_method();
        $this->form_fields['checkout_navigation_title'] = $this->field_checkout_navigation_title();
        $this->form_fields['auto_return'] = $this->field_auto_return();
        $this->form_fields['success_url'] = $this->field_success_url();
        $this->form_fields['failure_url'] = $this->field_failure_url();
        $this->form_fields['pending_url'] = $this->field_pending_url();
        $this->form_fields['payment_title'] = $this->field_payment_title();
        $this->form_fields['installments'] = $this->field_installments();
        foreach ($this->field_ex_payments() as $key => $value) {
        $this->form_fields[$key] = $value;
        }
        $this->form_fields['gateway_discount'] = $this->field_gateway_discount();
        $this->form_fields['two_cards_mode'] = $this->field_two_cards_mode();
        $this->form_fields['binary_mode'] = $this->field_binary_mode();   
    }
    
    public function field_enabled() {
    $enabled = array(
				'title' => __( 'Enable/Disable', 'woocommerce-mercadopago' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Basic Checkout', 'woocommerce-mercadopago' ),
				'default' => 'no'
			);
        return $enabled;
    }
    
    public function field_checkout_options_title() {
			$checkout_options_title = array(
				'title' => __( 'Checkout Interface: How checkout is shown', 'woocommerce-mercadopago' ),
				'type' => 'title'
			);
        return $checkout_options_title;
    }
    
    public function field_title() {
			$title = array(
				'title' => __( 'Title', 'woocommerce-mercadopago' ),
				'type' => 'text',
				'description' => __( 'Title shown to the client in the checkout.', 'woocommerce-mercadopago' ),
				'default' => __( 'Mercado Pago', 'woocommerce-mercadopago' )
			);
        return $title;
    }
    
    public function field_description() {
			$description = array(
				'title' => __( 'Description', 'woocommerce-mercadopago' ),
				'type' => 'textarea',
				'description' => __( 'Description shown to the client in the checkout.', 'woocommerce-mercadopago' ),
				'default' => __( 'Pay with Mercado Pago', 'woocommerce-mercadopago' )
			);
        return $description;
    }
    
    public function field_method() {
			$method = array(
				'title' => __( 'Integration Method', 'woocommerce-mercadopago' ),
				'type' => 'select',
				'description' => __( 'Select how your clients should interact with Mercado Pago. Modal Window (inside your store), Redirect (Client is redirected to Mercado Pago), or iFrame (an internal window is embedded to the page layout).', 'woocommerce-mercadopago' ),
				'default' => 'redirect',
				'options' => array(
                    'redirect' => __( 'Redirect', 'woocommerce-mercadopago' ),
					'modal' => __( 'Modal Window', 'woocommerce-mercadopago' )	
				)
			);
        return $method;
    }
    
    public function field_checkout_navigation_title() {
			$checkout_navigation_title = array(
				'title' => __( 'Checkout Navigation: How checkout redirections will behave', 'woocommerce-mercadopago' ),
				'type' => 'title'
			);
        return $checkout_navigation_title;
    }
    
    public function field_auto_return() {
			$auto_return = array(
				'title' => __( 'Auto Return', 'woocommerce-mercadopago' ),
				'type' => 'checkbox',
				'label' => __( 'Automatic Return After Payment', 'woocommerce-mercadopago' ),
				'default' => 'yes',
				'description' => __( 'After the payment, client is automatically redirected.', 'woocommerce-mercadopago' ),
			);
        return $auto_return;
    }
    
    public function field_success_url() {
        // Validate back URL.
		if ( ! empty( $this->success_url ) && filter_var( $this->success_url, FILTER_VALIDATE_URL ) === FALSE ) {
			$success_back_url_message = '<img width="14" height="14" src="' . plugins_url( 'assets/images/warning.png', plugin_dir_path( __FILE__ ) ) . '"> ' .
			__( 'This appears to be an invalid URL.', 'woocommerce-mercadopago' ) . ' ';
		} else {
			$success_back_url_message = __( 'Where customers should be redirected after a successful purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago' );
		}
        $success_url = array(
            'title' => __( 'Sucess URL', 'woocommerce-mercadopago' ),
            'type' => 'text',
            'description' => $success_back_url_message,
            'default' => ''
        );
        return $success_url;
    }
                
    public function field_failure_url() {
        if ( ! empty( $this->failure_url ) && filter_var( $this->failure_url, FILTER_VALIDATE_URL ) === FALSE ) {
			$fail_back_url_message = '<img width="14" height="14" src="' . plugins_url( 'assets/images/warning.png', plugin_dir_path( __FILE__ ) ) . '"> ' .
			__( 'This appears to be an invalid URL.', 'woocommerce-mercadopago' ) . ' ';
		} else {
			$fail_back_url_message = __( 'Where customers should be redirected after a failed purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago' );
		}
        $failure_url = array(
            'title' => __( 'Failure URL', 'woocommerce-mercadopago' ),
            'type' => 'text',
            'description' => $fail_back_url_message,
            'default' => ''
        );
        return $failure_url;
    }
    
    public function field_pending_url() {
         // Validate back URL.
        if ( ! empty( $this->pending_url ) && filter_var( $this->pending_url, FILTER_VALIDATE_URL ) === FALSE ) {
			$pending_back_url_message = '<img width="14" height="14" src="' . plugins_url( 'assets/images/warning.png', plugin_dir_path( __FILE__ ) ) . '"> ' .
			__( 'This appears to be an invalid URL.', 'woocommerce-mercadopago' ) . ' ';
		} else {
			$pending_back_url_message = __( 'Where customers should be redirected after a pending purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago' );
		}
        $pending_url = array(
            'title' => __( 'Pending URL', 'woocommerce-mercadopago' ),
            'type' => 'text',
            'description' => $pending_back_url_message,
            'default' => ''
        );
        return $pending_url;
    }
    
    public function field_payment_title() {
			$payment_title = array(
				'title' => __( 'Payment Options: How payment options behaves', 'woocommerce-mercadopago' ),
				'type' => 'title'
			);
        return $payment_title;
    }
    
    public function field_installments() {
        	$installments = array(
				'title' => __( 'Max installments', 'woocommerce-mercadopago' ),
				'type' => 'select',
				'description' => __( 'Select the max number of installments for your customers.', 'woocommerce-mercadopago' ),
				'default' => '24',
				'options' => array(
					'1' => __( '1x installment', 'woocommerce-mercadopago' ),
					'2' => __( '2x installmens', 'woocommerce-mercadopago' ),
					'3' => __( '3x installmens', 'woocommerce-mercadopago' ),
					'4' => __( '4x installmens', 'woocommerce-mercadopago' ),
					'5' => __( '5x installmens', 'woocommerce-mercadopago' ),
					'6' => __( '6x installmens', 'woocommerce-mercadopago' ),
					'10' => __( '10x installmens', 'woocommerce-mercadopago' ),
					'12' => __( '12x installmens', 'woocommerce-mercadopago' ),
					'15' => __( '15x installmens', 'woocommerce-mercadopago' ),
					'18' => __( '18x installmens', 'woocommerce-mercadopago' ),
					'24' => __( '24x installmens', 'woocommerce-mercadopago' )
				)
			);
        return $installments;
    }
    
    public function field_ex_payments() {
        $ex_payments = array();
        
        $get_payment_methods = get_option('_all_payment_methods_v0', '');
        if( ! empty($get_payment_methods) ) {
        $get_payment_methods = explode(',', $get_payment_methods);
        }
        
        $count_paument = 0;
        
        foreach ($get_payment_methods as $payment_method) {
            $count_paument++;
            
            $element = array(
                'label' => $payment_method,
                'id' => 'woocommerce_mercadopago_' . $payment_method,
                'default' => 'yes',
                'type' => 'checkbox'
            );
            if ($count_paument == 1) {
                $element['title'] = __('Payment Methods Accepted', 'woocommerce-mercadopago');
            }
            if ($count_paument == count($get_payment_methods)) {
                $element['description'] = __('Unselect the payment methods that you <strong>don\'t</strong> want to receive with Mercado Pago.', 'woocommerce-mercadopago');          
            }
            $ex_payments["ex_payments_" . $payment_method] = $element;
        }
            
        return $ex_payments;
    }
    
    public function field_gateway_discount() {
        $gateway_discount = array(
				'title' => __( 'Discount/Fee by Gateway', 'woocommerce-mercadopago' ),
				'type' => 'number',
				'description' => __( 'Give a percentual (-99 to 99) discount or fee for your customers if they use this payment gateway. Use negative for fees, positive for discounts.', 'woocommerce-mercadopago' ),
				'default' => '0',
				'custom_attributes' => array(
					'step' 	=> '0.01',
					'min'	=> '-99',
					'max' => '99'
				) 
			);
        return $gateway_discount;
    }
    
    public function field_two_cards_mode() {
        $two_cards_mode = array(
            'title' => __( 'Two Cards Mode', 'woocommerce-mercadopago' ),
            'type' => 'checkbox',
            'label' => __( 'Payments with Two Cards', 'woocommerce-mercadopago' ),
            'default' => ( $this->two_cards_mode == 'active' ? 'yes' : 'no' ),
            'description' => __( 'Your customer will be able to use two different cards to pay the order.', 'woocommerce-mercadopago' )
        );
        return $two_cards_mode;
    }
    
    public function field_binary_mode() {
        $binary_mode = array(
            'title' => __( 'Binary Mode', 'woocommerce-mercadopago' ),
            'type' => 'checkbox',
            'label' => __( 'Enable binary mode for checkout status', 'woocommerce-mercadopago' ),
            'default' => 'no',
            'description' => __( 'When charging a credit card, only [approved] or [reject] status will be taken.', 'woocommerce-mercadopago' )
		);
        return $binary_mode;
    }

     /*
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
                if ( $key == 'two_cards_mode' ) {
                    // We dont save two card mode as it should come from api.
                    unset( $this->settings[$key] );
                    $this->two_cards_mode = ( $value == 'yes' ? 'active' : 'inactive' );
                }
                elseif ( $key == 'gateway_discount') {
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
        $infra_data = WC_WooMercadoPago_Module::get_common_settings();
        $infra_data['checkout_basic'] = ( $this->settings['enabled'] == 'yes' ? 'true' : 'false' );
		$infra_data['two_cards'] = ( $this->two_cards_mode == 'active' ? 'true' : 'false' );
        return $infra_data;
    }
    
    /*
     * ========================================================================
     * HANDLES ORDER
     * ========================================================================
     *
	 * Summary: Show the custom renderization for the checkout.
	 * Description: Order page and this generates the form that shows the pay button. This step
	 * generates the form to proceed to checkout.
	 * @return the html to be rendered.
	 */

	public function render_order_form( $order_id ) {

		$order = wc_get_order( $order_id );
		$url = $this->create_url( $order );
		
		$banner_url = get_option( '_mp_custom_banner' );
		if ( ! isset( $banner_url ) || empty( $banner_url ) ) {
			$banner_url = $this->site_data['checkout_banner'];
		}

		if ( 'modal' == $this->method && $url ) {

			$this->write_log( __FUNCTION__, 'rendering Mercado Pago lightbox (modal window).' );

			// ===== The checkout is made by displaying a modal to the customer =====
			$html = '<style type="text/css">
						#MP-Checkout-dialog #MP-Checkout-IFrame { bottom: -28px !important; height: 590px !important; }
					</style>';
			$html .= '<script type="text/javascript" src="https://secure.mlstatic.com/mptools/render.js"></script>
					<script type="text/javascript">
						(function() { $MPC.openCheckout({ url: "' . esc_url( $url ) . '", mode: "modal" }); })();
					</script>';
			$html .= '<img width="468" height="60" src="' . $banner_url . '">';
			$html .= '<p></p><p>' . wordwrap(
						__( 'Thank you for your order. Please, proceed with your payment clicking in the bellow button.', 'woocommerce-mercadopago' ),
						60, '<br>'
					) . '</p>
					<a id="submit-payment" href="' . esc_url( $url ) . '" name="MP-Checkout" class="button alt" mp-mode="modal">' .
						__( 'Pay with Mercado Pago', 'woocommerce-mercadopago' ) .
					'</a> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' .
						__( 'Cancel order &amp; Clear cart', 'woocommerce-mercadopago' ) .
					'</a>';
			return $html;
			// ===== The checkout is made by displaying a modal to the customer =====

		} else {

			$this->write_log( __FUNCTION__, 'unable to build Mercado Pago checkout URL.' );

			// ===== Reaching at this point means that the URL could not be build by some reason =====
			$html = '<p>' .
						__( 'An error occurred when proccessing your payment. Please try again or contact us for assistence.', 'woocommerce-mercadopago' ) .
					'</p>' .
					'<a class="button" href="' . esc_url( $order->get_checkout_payment_url() ) . '">' .
						__( 'Click to try again', 'woocommerce-mercadopago' ) .
					'</a>
			';
			return $html;
			// ===== Reaching at this point means that the URL could not be build by some reason =====

		}

	}

     /*
	 * ========================================================================
	 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
	 * ========================================================================
	 */
    
    public function payment_fields() {
		// basic checkout
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}
		if ( $this->supports( 'default_credit_card_form' ) ) {
			$this->credit_card_form();
		}
	}
    
    /**
     * ========================================================================
	 * PROCESS PAYMENT.
	 * ========================================================================
     * 
	 * Summary: Handle the payment and processing the order.
	 * Description: First step occurs when the customer selects Mercado Pago and proceed to checkout.
	 * This method verify which integration method was selected and makes the build for the checkout
	 * URL.
	 * @return an array containing the result of the processment and the URL to redirect.
	 */
    
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( method_exists( $order, 'update_meta_data' ) ) {
			$order->update_meta_data( '_used_gateway', get_class($this) );
			$order->save();
		} else {
 			update_post_meta( $order_id, '_used_gateway', get_class($this) );
 		}

		if ( 'redirect' == $this->method ) {
			$this->write_log( __FUNCTION__, 'customer being redirected to Mercado Pago.' );
			return array(
				'result' => 'success',
				'redirect' => $this->create_url( $order )
			);
		} elseif ( 'modal' == $this->method ) {
			$this->write_log( __FUNCTION__, 'preparing to render Mercado Pago checkout view.' );
			return array(
				'result' => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);
		}

	}
    
    /**
	 * Summary: Build Mercado Pago preference.
	 * Description: Create Mercado Pago preference and get init_point URL based in the order options
	 * from the cart.
	 * @return the preference object.
	 */
	public function build_payment_preference( $order ) {

		$items = array();
		$currency_ratio = 1;
		$order_content = array();
		$selected_shipping = $order->get_shipping_method();
        
        $currency_ratio = 1;
        $_mp_currency_conversion_v1 = get_option( '_mp_currency_conversion_v1', '' );
        if ( ! empty( $_mp_currency_conversion_v1 ) ) {
            $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate( $this->site_data['currency'] );
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
					$line_amount = $item['line_total'] + $item['line_tax'];
					$method_discount = $line_amount * ( $this->gateway_discount / 100 );

					array_push( $order_content, $product_title . ' x ' . $item['qty'] );
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
							floor( ( $line_amount - $method_discount ) * $currency_ratio ) :
							floor( ( $line_amount - $method_discount ) * $currency_ratio * 100 ) / 100,
						'currency_id' => $this->site_data['currency']
					) );
				}
			}

			// If we're not using Mercado Envios, shipping cost is added as an item in the order, preventing
			// Mercado Pago Javascript to show shipment setup twice.
			$ship_amount = $order->get_total_shipping() + $order->get_shipping_tax();
			if ( strpos( $selected_shipping, 'Mercado Envios' ) !== 0 && $ship_amount > 0 ) {
				array_push( $order_content, __( 'Shipping service used by store', 'woocommerce-mercadopago' ) );
				array_push( $items, array(
					'title' => __( 'Shipping service used by store', 'woocommerce-mercadopago' ),
					'description' => __( 'Shipping service used by store', 'woocommerce-mercadopago' ),
					'category_id' => get_option( '_mp_category_name', 'others' ),
					'quantity' => 1,
					'unit_price' => ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
						floor( $ship_amount * $currency_ratio ) :
						floor( $ship_amount * $currency_ratio * 100 ) / 100,
					'currency_id' => $this->site_data['currency']
				) );
			}
			$items[0]['title'] = implode( ', ', $order_content );
		}

		// Create and setup payment options.
		$excluded_payment_methods = array();
		if ( is_array( $this->ex_payments ) && count($this->ex_payments) != 0 ) {
			foreach ( $this->ex_payments as $excluded ) {
				array_push( $excluded_payment_methods, array(
					'id' => $excluded
				) );
			}
		}
		$payment_methods = array(
			'installments' => (int) $this->installments,
			'default_installments' => 1,
			'excluded_payment_methods' => $excluded_payment_methods
		);

		// Create Mercado Pago preference.
		$preferences = array(
			'items' => $items,
			'payer' => ( method_exists( $order, 'get_id' ) ?
				array( // Support to WooCommerce 3.0.
					'name' => html_entity_decode( $order->get_billing_first_name() ),
					'surname' => html_entity_decode( $order->get_billing_last_name() ),
					'email' => $order->get_billing_email(),
					'phone' => array(
						'number' => $order->get_billing_phone()
					),
					'address' => array(
						'street_name' => html_entity_decode(
							$order->get_billing_address_1() . ' / ' .
							$order->get_billing_city() . ' ' .
							$order->get_billing_state() . ' ' .
							$order->get_billing_country()
						),
						'zip_code' => $order->get_billing_postcode()
					)
				) :
				array( // In case that we're not with WooCommerce 3.0.
					'name' => html_entity_decode( $order->billing_first_name ),
					'surname' => html_entity_decode( $order->billing_last_name ),
					'email' => $order->billing_email,
					'phone'	=> array(
						'number' => $order->billing_phone
					),
					'address' => array(
						'street_name' => html_entity_decode(
							$order->billing_address_1 . ' / ' .
							$order->billing_city . ' ' .
							$order->billing_state . ' ' .
							$order->billing_country
						),
						'zip_code' => $order->billing_postcode
					)
				)
			),
			'back_urls' => array(
				'success' => empty( $this->success_url ) ?
					WC_WooMercadoPago_Module::fix_url_ampersand(
						esc_url( $this->get_return_url( $order ) )
					) :
					$this->success_url,
				'failure' => empty( $this->failure_url ) ?
					WC_WooMercadoPago_Module::fix_url_ampersand(
						esc_url( $order->get_cancel_order_url() )
					) :
					$this->failure_url,
				'pending' => empty( $this->pending_url ) ?
					WC_WooMercadoPago_Module::fix_url_ampersand(
						esc_url( $this->get_return_url( $order) )
					) : $this->pending_url
			),
			//'marketplace' =>
			//'marketplace_fee' =>
			'shipments' => array(
				//'cost' =>
				//'mode' =>
				'receiver_address' => ( method_exists( $order, 'get_id' ) ?
					array(
						'zip_code' => $order->get_shipping_postcode(),
						//'street_number' =>
						'street_name' => html_entity_decode(
							$order->get_shipping_address_1() . ' ' .
							$order->get_shipping_city() . ' ' .
							$order->get_shipping_state() . ' ' .
							$order->get_shipping_country()
						),
						//'floor' =>
						'apartment' => $order->get_shipping_address_2()
					) :
					array(
						'zip_code' => $order->shipping_postcode,
						//'street_number' =>
						'street_name' => html_entity_decode(
							$order->shipping_address_1 . ' ' .
							$order->shipping_city . ' ' .
							$order->shipping_state . ' ' .
							$order->shipping_country
						),
						//'floor' =>
						'apartment' => $order->shipping_address_2
					)
				),
			),
			'payment_methods' => $payment_methods,
			//'notification_url' =>
			'external_reference' => get_option( '_mp_store_identificator', 'WC-' ) .
				( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id ),
            'binary_mode' => ( $this->binary_mode == 'yes' ),
			//'additional_info' =>
			//'expires' =>
			//'expiration_date_from' =>
			//'expiration_date_to' =>
		);

		// If we're  using Mercado Envios, shipping cost should be setup in preferences.
		if ( strpos( $selected_shipping, 'Mercado Envios' ) === 0 && $order->get_total_shipping() + $order->get_shipping_tax() > 0 ) {
			$preferences['shipments']['mode'] = 'me2';
			foreach ( $order->get_shipping_methods() as $shipping ) {
				$preferences['shipments']['dimensions'] = $shipping['dimensions'];
				$preferences['shipments']['default_shipping_method'] = (int) $shipping['shipping_method_id'];
				$preferences['shipments']['free_methods'] = array();
				// Get shipping method id.
				$prepare_method_id = explode( ':', $shipping['method_id'] );
				// Get instance_id.
				$shipping_id = $prepare_method_id[count( $prepare_method_id ) - 1];
				//  TODO: Refactorto Get zone by instance_id.
				$shipping_zone = WC_Shipping_Zones::get_zone_by( 'instance_id', $shipping_id );
				// Get all shipping and filter by free_shipping (Mercado Envios).
				foreach ( $shipping_zone->get_shipping_methods() as $key => $shipping_object ) {
					// Check is a free method.
					if ( $shipping_object->get_option( 'free_shipping' ) == 'yes' ) {
						// Get shipping method id (Mercado Envios).
						$shipping_method_id = $shipping_object->get_shipping_method_id( $this->site_data['site_id'] );
						$preferences['shipments']['free_methods'][] = array( 'id' => (int) $shipping_method_id );
					}
				}
			}
		}

		// Do not set IPN url if it is a localhost.
		if ( ! strrpos( get_site_url(), 'localhost' ) ) {
			$notification_url = get_option( '_mp_custom_domain', '' );
			// Check if we have a custom URL.
			if ( empty( $notification_url ) || filter_var( $notification_url, FILTER_VALIDATE_URL ) === FALSE ) {
				$preferences['notification_url'] = WC()->api_request_url( 'WC_WooMercadoPago_BasicGateway' );
			} else {
				$preferences['notification_url'] = WC_WooMercadoPago_Module::fix_url_ampersand( esc_url(
					$notification_url . '/wc-api/WC_WooMercadoPago_BasicGateway/'
				) );
			}
		}

		// Set sponsor ID.
		$_test_user_v1 = get_option( '_test_user_v1', false );
		if ( ! $_test_user_v1 ) {
			$preferences['sponsor_id'] = WC_WooMercadoPago_Module::get_sponsor_id();
		}

		// Auto return options.
		if ( 'yes' == $this->auto_return ) {
			$preferences['auto_return'] = 'approved';
		}

		// Debug/log this preference.
		$this->write_log(
			__FUNCTION__,
			'preference created with following structure: ' .
			json_encode( $preferences, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
		);

		return $preferences;
	}
    
    protected function create_url( $order ) {
		// Creates the order parameters by checking the cart configuration.
		$preferences = $this->build_payment_preference( $order );
		// Create order preferences with Mercado Pago API request.
		try {
			$checkout_info = $this->mp->create_preference( json_encode( $preferences ) );
			if ( $checkout_info['status'] < 200 || $checkout_info['status'] >= 300 ) {
				// Mercado Pago throwed an error.
				$this->write_log(
					__FUNCTION__,
					'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']
				);
				return false;
			} elseif ( is_wp_error( $checkout_info ) ) {
				// WordPress throwed an error.
				$this->write_log(
					__FUNCTION__,
					'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']
				);
				return false;
			} else {
				// Obtain the URL.
				$this->write_log(
					__FUNCTION__,
					'payment link generated with success from mercado pago, with structure as follow: ' .
					json_encode( $checkout_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
				);
				// TODO: Verify sandbox availability.
				//if ( $this->sandbox ) {
				//	return $checkout_info['response']['sandbox_init_point'];
				//} else {
				return $checkout_info['response']['init_point'];
				//}
			}
		} catch ( MercadoPagoException $ex ) {
			// Something went wrong with the payment creation.
			$this->write_log(
				__FUNCTION__,
				'payment creation failed with exception: ' .
				json_encode( $ex, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
			);
			return false;
		}
	}
    

    
}
