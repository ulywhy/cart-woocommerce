<?php

class WC_WooMercadoPago_Hook_Ticket extends WC_WooMercadoPago_Hook_Abstract
{
    public function __construct($payment)
    {
        parent::__construct($payment);
    }

    /**
     *
     */
    public function loadHooks()
    {
        parent::loadHooks();
        add_action('wp_enqueue_scripts', array( $this, 'add_checkout_scripts' ));


        //add_action('valid_mercadopago_ticket_ipn_request', array( $this, 'successful_request' ));

        if ( ! empty( $this->payment->settings['enabled'] ) && $this->payment->settings['enabled'] == 'yes' ) {

                add_action('woocommerce_after_checkout_form', array( $this, 'add_mp_settings_script_ticket' ));
                add_action('woocommerce_thankyou_' . $this->payment->id, array( $this, 'update_mp_settings_script_ticket' ));
            }

    }

    /**
     *
     */
    public function add_mp_settings_script_ticket(){
        parent::add_mp_settings_script();
    }

    public function update_mp_settings_script_ticket( $order_id ) {
        parent::update_mp_settings_script($order_id);
        $order = wc_get_order( $order_id );
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
            __( 'Thank you for your order. Please, pay the ticket to get your order approved.', 'woocommerce-mercadopago' ) .
            '</p>' .
            '<p><iframe src="' . $transaction_details . '" style="width:100%; height:1000px;"></iframe></p>' .
            '<a id="submit-payment" target="_blank" href="' . $transaction_details . '" class="button alt"' .
            ' style="font-size:1.25rem; width:75%; height:48px; line-height:24px; text-align:center;">' .
            __( 'Print the Ticket', 'woocommerce-mercadopago' ) .
            '</a> ';
        $added_text = '<p>' . $html . '</p>';
        echo $added_text;
    }
}