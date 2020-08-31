<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WC_WooMercadoPago_Stock_Manager
 */
class WC_WooMercadoPago_Stock_Manager
{
    /**
     * WC_WooMercadoPago_Stock_Manager constructor.
     */
    public function __construct()
    {
        add_action('woocommerce_order_status_pending_to_cancelled', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_processing_to_cancelled', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_completed_to_cancelled', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_on-hold_to_cancelled', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_processing_to_refunded', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_completed_to_refunded', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
        add_action('woocommerce_order_status_on-hold_to_refunded', array('WC_WooMercadoPago_Stock_Manager', 'restore_stock_item'), 10, 1);
    }

    /**
     * @param $order_id
     */
    public static function restore_stock_item($order_id)
    {
        $order = new WC_Order($order_id);

        if (!get_option('woocommerce_manage_stock') == 'yes' && !sizeof($order->get_items()) > 0) {
            return;
        }

        foreach ($order->get_items() as $item) {
            if ($item['product_id'] > 0) {
                $_product = wc_get_product($item['product_id']);
                if ($_product && $_product->exists() && $_product->managing_stock()) {
                    $qty = apply_filters('woocommerce_order_item_quantity', $item['qty'], $order, $item);
                    wc_update_product_stock($_product, $qty, 'increase');
                    do_action('woocommerce_auto_stock_restored', $_product, $item);
                }
            }
        }
    }
}

new WC_WooMercadoPago_Stock_Manager();
