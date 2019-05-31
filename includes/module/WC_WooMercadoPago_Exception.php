<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MercadoPagoException
 */
class WC_WooMercadoPago_Exception extends Exception {

    /**
     * MercadoPagoException constructor.
     * @param $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct( $message, $code = 500, Exception $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
}