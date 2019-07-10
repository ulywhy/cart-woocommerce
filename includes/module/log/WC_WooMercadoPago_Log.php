<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_WooMercadoPago_Log
 */
class WC_WooMercadoPago_Log
{
    public static $instance = null;
    public $log;
    public $id;

    /**
     * WC_WooMercadoPago_Log constructor.
     */
    public function __construct()
    {
        $_mp_debug_mode = get_option( '_mp_debug_mode', '' );
        if (!empty ( $_mp_debug_mode )) {
            if ( class_exists( 'WC_Logger' ) ) {
                $this->log = new WC_Logger();
            } else {
                $this->log = WC_WooMercadoPago_Module::woocommerce_instance()->logger();
            }
            return $this->log;
        }
    }

    /**
     * @param null $id
     * @return WC_WooMercadoPago_Log|null
     */
    public static function init_mercado_pago_log($id = null)
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        $log = self::$instance;
        if (!empty($log)) {
            $log->setId($id);
        }

        return $log;
    }

    /**
     * @param $function
     * @param $message
     */
    public function write_log( $function, $message )
    {
        $_mp_debug_mode = get_option( '_mp_debug_mode', '' );
        if ( ! empty ( $_mp_debug_mode ) ) {
            $this->log->add($this->id,'[' . $function . ']: ' . $message);
        }
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


}