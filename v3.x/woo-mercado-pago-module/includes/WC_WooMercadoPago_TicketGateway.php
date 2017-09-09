<?php

/**
 * Part of Woo Mercado Pago Module
 * Author - Mercado Pago
 * Developer - Marcelo Tomio Hama / marcelo.hama@mercadolivre.com
 * Copyright - Copyright(c) MercadoPago [https://www.mercadopago.com]
 * License - https://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// This include Mercado Pago library SDK
require_once dirname( __FILE__ ) . '/sdk/lib/mercadopago.php';

/**
 * Summary: Extending from WooCommerce Payment Gateway class.
 * Description: This class implements Mercado Pago ticket payment method.
 * @since 3.0.0
 */
class WC_WooMercadoPago_TicketGateway extends WC_Payment_Gateway {

	public function __construct( $is_instance = false ) {
		
		// Mercao Pago instance.
		$this->site_data = WC_Woo_Mercado_Pago_Module::get_site_data( true );
		$this->mp = new MP(
			WC_Woo_Mercado_Pago_Module::get_module_version(),
			get_option( '_mp_access_token' )
		);

		// WooCommerce fields.
		$this->id = 'woo-mercado-pago-ticket';
		$this->supports = array( 'products', 'refunds' );
		$this->icon = apply_filters(
			'woocommerce_mercadopago_icon',
			plugins_url( 'assets/images/mplogo.png', plugin_dir_path( __FILE__ ) )
		);

		$this->method_title = __( 'Mercado Pago - Ticket', 'woo-mercado-pago-module' );
		$this->method_description = '<img width="200" height="52" src="' .
			plugins_url( 'assets/images/mplogo.png', plugin_dir_path( __FILE__ ) ) .
		'"><br><br><strong>' .
			__( 'We give you the possibility to adapt the payment experience you want to offer 100% in your website, mobile app or anywhere you want. You can build the design that best fits your business model, aiming to maximize conversion.', 'woo-mercado-pago-module' ) .
		'</strong>';

		$this->sandbox = get_option( '_mp_sandbox_mode', false );
		$this->mp->sandbox_mode( $this->sandbox );

		// How checkout is shown.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		// How checkout payment behaves.
		$this->coupon_mode        = $this->get_option( 'coupon_mode', 'no' );
		$this->stock_reduce_mode  = $this->get_option( 'stock_reduce_mode', 'no' );
		$this->gateway_discount   = $this->get_option( 'gateway_discount', 0 );
		
		// Logging and debug.
		$_mp_debug_mode = get_option( '_mp_debug_mode', '' );
		if ( ! empty ( $_mp_debug_mode ) ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = WC_Woo_Mercado_Pago_Module::woocommerce_instance()->logger();
			}
		}

		// Render our configuration page and init/load fields.
		$this->init_form_fields();
		$this->init_settings();

		// Used by IPN to receive IPN incomings.
		add_action(
			'woocommerce_api_wc_woomercadopago_ticketgateway',
			array( $this, 'check_ipn_response' )
		);
		// Used by IPN to process valid incomings.
		add_action(
			'valid_mercadopago_ticket_ipn_request',
			array( $this, 'successful_request' )
		);
		// process the cancel order meta box order action
		add_action(
			'woocommerce_order_action_cancel_order',
			array( $this, 'process_cancel_order_meta_box_actions' )
		);
		// Used in settings page to hook "save settings" action.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'custom_process_admin_options' )
		);
		// Scripts for custom checkout.
		add_action(
			'wp_enqueue_scripts',
			array( $this, 'add_checkout_scripts_ticket' )
		);
		// Apply the discounts.
		add_action(
			'woocommerce_cart_calculate_fees',
			array( $this, 'add_discount_ticket' ), 10
		);
		// Display discount in payment method title.
		add_filter(
			'woocommerce_gateway_title',
			array( $this, 'get_payment_method_title_ticket' ), 10, 2
		);
		// Customizes thank you page.
		add_filter(
			'woocommerce_thankyou_order_received_text',
			array( $this, 'show_ticket_button' ), 10, 2
		);

		if ( ! empty( $this->settings['enabled'] ) && $this->settings['enabled'] == 'yes' ) {
			if ( ! $is_instance ) {
				// Scripts for order configuration.
				add_action(
					'woocommerce_after_checkout_form',
					array( $this, 'add_mp_settings_script_ticket' )
				);
				// Checkout updates.
				add_action(
					'woocommerce_thankyou',
					array( $this, 'update_mp_settings_script_ticket' )
				);
			}
		}

	}

	/**
	 * Summary: Initialise Gateway Settings Form Fields.
	 * Description: Initialise Gateway settings form fields with a customized page.
	 */
	public function init_form_fields() {

		// Show message if credentials are not properly configured.
		$_site_id_v1 = get_option( '_site_id_v1', '' );
		if ( empty( $_site_id_v1 ) ) {
			$this->form_fields = array(
				'no_credentials_title' => array(
					'title' => sprintf(
						__( 'It appears that your credentials are not properly configured.<br/>Please, go to %s and configure it.', 'woo-mercado-pago-module' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=mercado-pago-settings' ) ) . '">' .
						__( 'Mercado Pago Settings', 'woo-mercado-pago-module' ) .
						'</a>'
					),
					'type' => 'title'
				),
			);
			return;
		}

		// This array draws each UI (text, selector, checkbox, label, etc).
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Ticket Payment Method', 'woo-mercado-pago-module' ),
				'default' => 'no'
			),
			'checkout_options_title' => array(
				'title' => __( 'Ticket Interface: How checkout is shown', 'woo-mercado-pago-module' ),
				'type' => 'title'
			),
			'title' => array(
				'title' => __( 'Title', 'woo-mercado-pago-module' ),
				'type' => 'text',
				'description' => __( 'Title shown to the client in the ticket.', 'woo-mercado-pago-module' ),
				'default' => __( 'Mercado Pago - Ticket', 'woo-mercado-pago-module' )
			),
			'description' => array(
				'title' => __( 'Description', 'woo-mercado-pago-module' ),
				'type' => 'textarea',
				'description' => __( 'Description shown to the client in the ticket.', 'woo-mercado-pago-module' ),
				'default' => __( 'Pay with Mercado Pago', 'woo-mercado-pago-module' )
			),
			'payment_title' => array(
				'title' => __( 'Payment Options: How payment options behaves', 'woo-mercado-pago-module' ),
				'type' => 'title'
			),
			'coupon_mode' => array(
				'title' => __( 'Coupons', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Enable coupons of discounts', 'woo-mercado-pago-module' ),
				'default' => 'no',
				'description' => __( 'If there is a Mercado Pago campaign, allow your store to give discounts to customers.', 'woo-mercado-pago-module' )
			),
			'stock_reduce_mode' => array(
				'title' => __( 'Stock Reduce', 'woo-mercado-pago-module' ),
				'type' => 'checkbox',
				'label' => __( 'Reduce Stock in Order Generation', 'woo-mercado-pago-module' ),
				'default' => 'no',
				'description' => __( 'Enable this to reduce the stock on order creation. Disable this to reduce <strong>after</strong> the payment approval.', 'woo-mercado-pago-module' )
			),
			'gateway_discount' => array(
				'title' => __( 'Discount by Gateway', 'woo-mercado-pago-module' ),
				'type' => 'number',
				'description' => __( 'Give a percentual (0 to 100) discount for your customers if they use this payment gateway.', 'woo-mercado-pago-module' ),
				'default' => '0'
			)
		);

	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the
	 * erroring field out.
	 * @return bool was anything saved?
	 */
	public function custom_process_admin_options() {
		$this->init_settings();
		$post_data = $this->get_post_data();
		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				$value = $this->get_field_value( $key, $field, $post_data );
				if ( $key == 'gateway_discount') {
					if ( ! is_numeric( $value ) || empty ( $value ) ) {
						$this->settings[$key] = 0;
					} else {
						if ( $value < 0 || $value >= 100 || empty ( $value ) ) {
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
		$_site_id_v1 = get_option( '_site_id_v1', '' );
		$is_test_user = get_option( '_test_user_v1', false );
		if ( ! empty( $_site_id_v1 ) && ! $is_test_user ) {
			// Create MP instance.
			$mp = new MP(
				WC_Woo_Mercado_Pago_Module::get_module_version(),
				get_option( '_mp_access_token' )
			);
			// Analytics.
			$infra_data = WC_Woo_Mercado_Pago_Module::get_common_settings();
			$infra_data['checkout_custom_ticket'] = ( $this->settings['enabled'] == 'yes' ? 'true' : 'false' );
			$infra_data['checkout_custom_ticket_coupon'] = ( $this->settings['coupon_mode'] == 'yes' ? 'true' : 'false' );
			$response = $mp->analytics_save_settings( $infra_data );
		}
		// Apply updates.
		return update_option(
			$this->get_option_key(),
			apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings )
		);
	}

	/**
	 * Handles the manual order refunding in server-side.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$payments = get_post_meta( $order_id, '_Mercado_Pago_Payment_IDs', true );

		// Validate.
		if ( $this->mp == null || empty( $payments ) ) {
			$this->write_log( __FUNCTION__, 'no payments or credentials invalid' );
			return false;
		}

		// Processing data about this refund.
		$total_available = 0;
		$payment_structs = array();
		$payment_ids = explode( ', ', $payments );
		foreach ( $payment_ids as $p_id ) {
			$p = get_post_meta( $order_id, 'Mercado Pago - Payment ' . $p_id, true );
			$p = explode( '/', $p );
			$paid_arr = explode( ' ', substr( $p[2], 1, -1 ) );
			$paid = ( (float) $paid_arr[1] );
			$refund_arr = explode( ' ', substr( $p[3], 1, -1 ) );
			$refund = ( (float) $refund_arr[1] );
			$p_struct = array( 'id' => $p_id, 'available_to_refund' => $paid - $refund );
			$total_available += $paid - $refund;
			$payment_structs[] = $p_struct;
		}
		$this->write_log( __FUNCTION__,
			'refunding ' . $amount . ' because of ' . $reason . ' and payments ' .
			json_encode( $payment_structs, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE )
		);

		// Do not allow refund more than available or invalid amounts.
		if ( $amount > $total_available || $amount <= 0 ) {
			return false;
		}

		// Iteratively refunfind amount, taking in consideration multiple payments.
		$remaining_to_refund = $amount;
		foreach ( $payment_structs as $to_refund ) {
			if ( $remaining_to_refund <= $to_refund['available_to_refund'] ) {
				// We want to refund an amount that is less than the available for this payment, so we
				// can just refund and return.
				$response = $this->mp->partial_refund_payment(
					$to_refund['id'], $remaining_to_refund,
					$reason, $this->invoice_prefix . $order_id
				);
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'refund payment of id ' . $p_id . ' => ' .
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
				if ( $status >= 200 && $status < 300 ) {
					return true;
				} else {
					return false;
				}
			} elseif ( $to_refund['available_to_refund'] > 0 ) {
				// We want to refund an amount that exceeds the available for this payment, so we
				// totally refund this payment, and try to complete refund in other/next payments.
				$response = $this->mp->partial_refund_payment(
					$to_refund['id'], $to_refund['available_to_refund'],
					$reason, $this->invoice_prefix . $order_id
				);
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'refund payment of id ' . $p_id . ' => ' .
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
				if ( $status < 200 || $status >= 300 ) {
					return false;
				}
				$remaining_to_refund -= $to_refund['available_to_refund'];
			}
			if ( $remaining_to_refund == 0 )
				return true;
		}

		// Reaching here means that there we run out of payments, and there is an amount
		// remaining to be refund, which is impossible as it implies refunding more than
		// available on paid amounts.
		return false;
	}

	/**
	 * Handles the manual order cancellation in server-side.
	 */
	public function process_cancel_order_meta_box_actions( $order ) {

		$used_gateway = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_used_gateway' ) :
			get_post_meta( $order->id, '_used_gateway', true );
		$payments = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_Mercado_Pago_Payment_IDs' ) :
			get_post_meta( $order->id, '_Mercado_Pago_Payment_IDs',	true );

		// A watchdog to prevent operations from other gateways.
		if ( $used_gateway != 'WC_WooMercadoPago_TicketGateway' ) {
			return;
		}

		$this->write_log( __FUNCTION__, 'cancelling payments for ' . $payments );

		// Canceling the order and all of its payments.
		if ( $this->mp != null && ! empty( $payments ) ) {
			$payment_ids = explode( ', ', $payments );
			foreach ( $payment_ids as $p_id ) {
				$response = $this->mp->cancel_payment( $p_id );
				$message = $response['response']['message'];
				$status = $response['status'];
				$this->write_log( __FUNCTION__,
					'cancel payment of id ' . $p_id . ' => ' . 
					( $status >= 200 && $status < 300 ? 'SUCCESS' : 'FAIL - ' . $message )
				);
			}
		} else {
			$this->write_log( __FUNCTION__, 'no payments or credentials invalid' );
		}
	}

	// Write log.
	private function write_log( $function, $message ) {
		$_mp_debug_mode = get_option( '_mp_debug_mode', '' );
		if ( ! empty ( $_mp_debug_mode ) ) {
			$this->log->add(
				$this->id,
				'[' . $function . ']: ' . $message
			);
		}
	}

	/*
	 * ========================================================================
	 * CHECKOUT BUSINESS RULES (CLIENT SIDE)
	 * ========================================================================
	 */

	public function add_mp_settings_script_ticket() {
		$client_id = WC_Woo_Mercado_Pago_Module::get_client_id( get_option( '_mp_access_token' ) );
		$is_test_user = get_option( '_test_user_v1', false );
		if ( ! empty( $client_id ) && ! $is_test_user ) {
			$w = WC_Woo_Mercado_Pago_Module::woocommerce_instance();
			$available_payments = array();
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			foreach ( $gateways as $g ) {
				$available_payments[] = $g->id;
			}
			$available_payments = str_replace( '-', '_', implode( ', ', $available_payments ) );
			if ( wp_get_current_user()->ID != 0 ) {
				$logged_user_email = wp_get_current_user()->user_email;
			} else {
				$logged_user_email = null;
			}
			?>
			<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setToken( '<?php echo $client_id; ?>' );
				MA.setPlatform( 'WooCommerce' );
				MA.setPlatformVersion( '<?php echo $w->version; ?>' );
				MA.setModuleVersion( '<?php echo WC_Woo_Mercado_Pago_Module::VERSION; ?>' );
				MA.setPayerEmail( '<?php echo ( $logged_user_email != null ? $logged_user_email : "" ); ?>' );
				MA.setUserLogged( <?php echo ( empty( $logged_user_email ) ? 0 : 1 ); ?> );
				MA.setInstalledModules( '<?php echo $available_payments; ?>' );
				MA.post();
			</script>
			<?php
		}
	}

	public function update_mp_settings_script_ticket( $order_id ) {
		$access_token = get_option( '_mp_access_token' );
		$is_test_user = get_option( '_test_user_v1', false );
		if ( ! empty( $access_token ) && ! $is_test_user ) {
			if ( get_post_meta( $order_id, '_used_gateway', true ) != 'WC_WooMercadoPago_TicketGateway' ) {
				return;
			}
			$this->write_log( __FUNCTION__, 'updating order of ID ' . $order_id );
			echo '<script src="https://secure.mlstatic.com/modules/javascript/analytics.js"></script>
			<script type="text/javascript">
				var MA = ModuleAnalytics;
				MA.setToken( ' . $access_token . ' );
				MA.setPaymentType("ticket");
				MA.setCheckoutType("custom");
				MA.put();
			</script>';
		}
	}

	public function add_checkout_scripts_ticket() {
		if ( is_checkout() && $this->is_available() ) {
			if ( ! get_query_var( 'order-received' ) ) {
				/* TODO: separate javascript from html template
				$logged_user_email = ( wp_get_current_user()->ID != 0 ) ? wp_get_current_user()->user_email : null;
				$discount_action_url = get_site_url() . '/index.php/woo-mercado-pago-module/?wc-api=WC_WooMercadoPago_TicketGateway';
				*/
				wp_enqueue_style(
					'woocommerce-mercadopago-style',
					plugins_url( 'assets/css/custom_checkout_mercadopago.css', plugin_dir_path( __FILE__ ) )
				);
				wp_enqueue_script(
					'woocommerce-mercadopago-ticket-js',
					'https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js'
				);
				/* TODO: separate javascript from html template
				wp_enqueue_script(
					'woo-mercado-pago-module-ticket-js',
					plugins_url( 'assets/js/ticket.js', plugin_dir_path( __FILE__ ) ),
					array( 'woocommerce-mercadopago-ticket-js' ),
					WC_Woo_Mercado_Pago_Module::VERSION,
					true
				);
				wp_localize_script(
					'woo-mercado-pago-module-ticket-js',
					'wc_mercadopago_ticket_params',
					array(
						'site_id'             => get_option( '_site_id_v1' ),
						'public_key'          => get_option( '_mp_public_key' ),
						'coupon_mode'         => isset( $logged_user_email ) ? $this->coupon_mode : 'no',
						'discount_action_url' => $discount_action_url,
						'payer_email'         => $logged_user_email,
						// ===
						'apply'               => __( 'Apply', 'woo-mercado-pago-module' ),
						'remove'              => __( 'Remove', 'woo-mercado-pago-module' ),
						'coupon_empty'        => __( 'Please, inform your coupon code', 'woo-mercado-pago-module' ),
						'discount_info1'      => __( 'You will save', 'woo-mercado-pago-module' ),
						'discount_info2'      => __( 'with discount from', 'woo-mercado-pago-module' ),
						'discount_info3'      => __( 'Total of your purchase:', 'woo-mercado-pago-module' ),
						'discount_info4'      => __( 'Total of your purchase with discount:', 'woo-mercado-pago-module' ),
						'discount_info5'      => __( '*Uppon payment approval', 'woo-mercado-pago-module' ),
						'discount_info6'      => __( 'Terms and Conditions of Use', 'woo-mercado-pago-module' ),
						// ===
						'images_path'         => plugins_url( 'assets/images/', plugin_dir_path( __FILE__ ) )
					)
				);
				*/
			}
		}
	}

	public function show_ticket_button( $thankyoutext, $order ) {

		$used_gateway = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_used_gateway' ) :
			get_post_meta( $order->id, '_used_gateway', true );
		$transaction_details = ( method_exists( $order, 'get_meta' ) ) ?
			$order->get_meta( '_transaction_details_ticket' ) :
			get_post_meta( $order->id, '_transaction_details_ticket', true );

		// A watchdog to prevent operations from other gateways.
		if ( $used_gateway != 'WC_WooMercadoPago_TicketGateway' || empty( $transaction_details ) ) {
			return;
		}

		$html = '<p>' .
					__( 'Thank you for your order. Please, pay the ticket to get your order approved.', 'woo-mercado-pago-module' ) .
				'</p>' .
				'<p><iframe src="' . $transaction_details . '" style="width:100%; height:1000px;"></iframe></p>' .
				'<a id="submit-payment" target="_blank" href="' . $transaction_details . '" class="button alt"' .
				' style="font-size:1.25rem; width:75%; height:48px; line-height:24px; text-align:center;">' .
					__( 'Print the Ticket', 'woo-mercado-pago-module' ) .
				'</a> ';
		$added_text = '<p>' . $html . '</p>';
		return $added_text;
	}

	public function payment_fields() {
		
		$amount = $this->get_order_total();
		$logged_user_email = ( wp_get_current_user()->ID != 0 ) ? wp_get_current_user()->user_email : null;
		$customer = isset( $logged_user_email ) ? $this->mp->get_or_create_customer( $logged_user_email ) : null;
		$discount_action_url = get_site_url() . '/index.php/woo-mercado-pago-module/?wc-api=WC_WooMercadoPago_TicketGateway';
		$address = get_user_meta( wp_get_current_user()->ID, 'shipping_address_1', true );
		$address_2 = get_user_meta( wp_get_current_user()->ID, 'shipping_address_2', true );
		$address .= ( ! empty( $address_2 ) ? ' - ' . $address_2 : '' );
		$country = get_user_meta( wp_get_current_user()->ID, 'shipping_country', true );
		$address .= ( ! empty( $country ) ? ' - ' . $country : '' );

		$currency_ratio = 1;
		$_mp_currency_conversion_v1 = get_option( '_mp_currency_conversion_v1', '' );
		if ( ! empty( $_mp_currency_conversion_v1 ) ) {
			$currency_ratio = WC_Woo_Mercado_Pago_Module::get_conversion_rate( $this->site_data['currency'] );
			$currency_ratio = $currency_ratio > 0 ? $currency_ratio : 1;
		}

		$parameters = array(
			'amount'                 => $amount,
			'payment_methods'        => json_decode( get_option( '_all_payment_methods_ticket', '[]' ), true ),
			// ===
			'site_id'                => get_option( '_site_id_v1' ),
			'coupon_mode'            => isset( $logged_user_email ) ? $this->coupon_mode : 'no',
			'discount_action_url'    => $discount_action_url,
			'payer_email'            => $logged_user_email,
			// ===
			'images_path'            => plugins_url( 'assets/images/', plugin_dir_path( __FILE__ ) ),
			'currency_ratio'         => $currency_ratio,
			'woocommerce_currency'   => get_woocommerce_currency(),
			'account_currency'       => $this->site_data['currency'],
			// ===
			'febraban' => ( wp_get_current_user()->ID != 0 ) ?
				array(
					'firstname' => wp_get_current_user()->user_firstname,
					'lastname' => wp_get_current_user()->user_lastname,
					'docNumber' => '',
					'address' => $address,
					'number' => '',
					'city' => get_user_meta( wp_get_current_user()->ID, 'shipping_city', true ),
					'state' => get_user_meta( wp_get_current_user()->ID, 'shipping_state', true ),
					'zipcode' => get_user_meta( wp_get_current_user()->ID, 'shipping_postcode', true )
				) :
				array(
					'firstname' => '', 'lastname' => '', 'docNumber' => '', 'address' => '',
					'number' => '', 'city' => '', 'state' => '', 'zipcode' => ''
				)
		);

		wc_get_template(
			'ticket/ticket-form.php',
			$parameters,
			'woo/mercado/pago/module/',
			WC_Woo_Mercado_Pago_Module::get_templates_path()
		);
	}

	/**
	* Summary: Handle the payment and processing the order.
	* Description: This function is called after we click on [place_order] button, and each field is
	* passed to this function through $_POST variable.
	* @return an array containing the result of the processment and the URL to redirect.
	*/
	public function process_payment( $order_id ) {

		if ( ! isset( $_POST['mercadopago_ticket'] ) ) {
			return;
		}
		$ticket_checkout = $_POST['mercadopago_ticket'];

		$order = wc_get_order( $order_id );
		if ( method_exists( $order, 'update_meta_data' ) ) {
			$order->update_meta_data( '_used_gateway', 'WC_WooMercadoPago_TicketGateway' );
			$order->save();
		} else {
 			update_post_meta( $order_id, '_used_gateway', 'WC_WooMercadoPago_TicketGateway' );
 		}

 		if ( isset( $ticket_checkout['amount'] ) && ! empty( $ticket_checkout['amount'] ) &&
			isset( $ticket_checkout['paymentMethodId'] ) && ! empty( $ticket_checkout['paymentMethodId'] ) ) {
			// Check for brazilian FEBRABAN rules.
			if ( get_option( '_site_id_v1' ) == 'MLB' ) {
				if ( isset( $ticket_checkout['firstname'] ) && ! empty( $ticket_checkout['firstname'] ) &&
					isset( $ticket_checkout['lastname'] ) && ! empty( $ticket_checkout['lastname'] ) &&
					isset( $ticket_checkout['docNumber'] ) && ! empty( $ticket_checkout['docNumber'] ) &&
					isset( $ticket_checkout['address'] ) && ! empty( $ticket_checkout['address'] ) &&
					isset( $ticket_checkout['number'] ) && ! empty( $ticket_checkout['number'] ) &&
					isset( $ticket_checkout['city'] ) && ! empty( $ticket_checkout['city'] ) &&
					isset( $ticket_checkout['state'] ) && ! empty( $ticket_checkout['state'] ) &&
					isset( $ticket_checkout['zipcode'] ) && ! empty( $ticket_checkout['zipcode'] ) ) {
					return $this->create_url( $order, $ticket_checkout );
				} else {
					wc_add_notice(
						'<p>' .
							__( 'A problem was occurred when processing your payment. Are you sure you have correctly filled all information in the checkout form?', 'woo-mercado-pago-module' ) .
						'</p>',
						'error'
					);
					return array(
						'result' => 'fail',
						'redirect' => '',
					);
				}
			}
			return $this->create_url( $order, $ticket_checkout );
		} else {
			// Process when fields are imcomplete.
			wc_add_notice(
				'<p>' .
					__( 'A problem was occurred when processing your payment. Please, try again.', 'woo-mercado-pago-module' ) .
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
	/*private function build_payment_preference( $order, $ticket_checkout ) {
	}*/

	/*protected function create_url( $order, $ticket_checkout ) {
	}*/

	/**
	* Summary: Receive post data and applies a discount based in the received values.
	* Description: Receive post data and applies a discount based in the received values.
	*/
	public function add_discount_ticket() {

		if ( ! isset( $_POST['mercadopago_ticket'] ) ) {
			return;
		}

		if ( is_admin() && ! defined( 'DOING_AJAX' ) || is_cart() ) {
			return;
		}

		$ticket_checkout = $_POST['mercadopago_ticket'];
		if ( isset( $ticket_checkout['discount'] ) && ! empty( $ticket_checkout['discount'] ) &&
			isset( $ticket_checkout['coupon_code'] ) && ! empty( $ticket_checkout['coupon_code'] ) &&
			$ticket_checkout['discount'] > 0 && WC()->session->chosen_payment_method == 'woo-mercado-pago-ticket' ) {

			$this->write_log( __FUNCTION__, 'ticket checkout trying to apply discount...' );
			
			$value = ( $this->site_data['currency'] == 'COP' || $this->site_data['currency'] == 'CLP' ) ?
				floor( $ticket_checkout['discount'] / $ticket_checkout['currency_ratio'] ) :
				floor( $ticket_checkout['discount'] / $ticket_checkout['currency_ratio'] * 100 ) / 100;
			global $woocommerce;
			if ( apply_filters(
				'wc_mercadopagoticket_module_apply_discount',
				0 < $value, $woocommerce->cart )
			) {
				$woocommerce->cart->add_fee( sprintf(
					__( 'Discount for %s coupon', 'woo-mercado-pago-module' ),
					esc_attr( $ticket_checkout['campaign']
					) ), ( $value * -1 ), false
				);
			}
		}

	}

	// Display the discount in payment method title.
	public function get_payment_method_title_ticket( $title, $id ) {

		if ( ! is_checkout() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return $title;
		}

		if ( $title != $this->title || $this->gateway_discount == 0 ) {
			return $title;
		}

		$total = (float) WC()->cart->subtotal;
		if ( is_numeric( $this->gateway_discount ) ) {
			if ( $this->gateway_discount >= 0 && $this->gateway_discount < 100 ) {
				$price_percent = $this->gateway_discount / 100;
				if ( $price_percent > 0 ) {
					$title .= ' (' . __( 'Discount of', 'woo-mercado-pago-module' ) . ' ' .
						strip_tags( wc_price( $total * $price_percent ) ) . ' )';
				}
			}
		}

		return $title;
	}

	/*
	 * ========================================================================
	 * AUXILIARY AND FEEDBACK METHODS (SERVER SIDE)
	 * ========================================================================
	 */

	// Called automatically by WooCommerce, verify if Module is available to use.
	public function is_available() {
		if ( ! did_action( 'wp_loaded' ) ) {
			return false;
		}
		global $woocommerce;
		$w_cart = $woocommerce->cart;
		// Check if we have SSL.
		if ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' ) {
			return false;
		}
		// Check for recurrent product checkout.
		if ( isset( $w_cart ) ) {
			if ( WC_Woo_Mercado_Pago_Module::is_subscription( $w_cart->get_cart() ) ) {
				return false;
			}
		}
		// Check if there are available payments with ticket.
		$payment_methods = json_decode( get_option( '_all_payment_methods_ticket', '[]' ), true );
		if ( count( $payment_methods ) == 0 ) {
			return false;
		}
		// Check if this gateway is enabled and well configured.
		$_mp_public_key = get_option( '_mp_public_key' );
		$_mp_access_token = get_option( '_mp_access_token' );
		$_site_id_v1 = get_option( '_site_id_v1' );
		$available = ( 'yes' == $this->settings['enabled'] ) &&
			! empty( $_mp_public_key ) &&
			! empty( $_mp_access_token ) &&
			! empty( $_site_id_v1 );
		return $available;
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
	}

	/**
	 * Summary: Properly handles each case of notification, based in payment status.
	 * Description: Properly handles each case of notification, based in payment status.
	 */
	public function successful_request( $data ) {
	}

}

new WC_WooMercadoPago_TicketGateway( true );
