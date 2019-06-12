<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_WooMercadoPago_Payments
 */
class WC_WooMercadoPago_PaymentAbstract extends WC_Payment_Gateway
{


    public $id;
    public $method_title;
    public $title;
    public $gateway_discount;
    public $ex_payments = array();
    public $method;
    public $method_description;
    public $auto_return;
    public $success_url;
    public $failure_url;
    public $pending_url;
    public $installments;
    public $two_cards_mode;
    public $form_fields;
    public $coupon_mode;
    public $payment_type;
    public $checkout_type;
    public $stock_reduce_mode;
    public $date_expiration;
    public $hook;

    public $supports;
    public $description;
    public $icon;
    public $binary_mode;
    public $site_data;
    public $log;
    public $sandbox;
    public $mp;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->description = $this->get_option('description');
        $this->binary_mode = get_option('binary_mode', 'no');
        $this->gateway_discount = get_option('gateway_discount', 0);
        $this->sandbox = get_option('_mp_sandbox_mode', false);
        $this->supports = array('products', 'refunds');
        $this->icon = $this->getMpIcon();
        $this->site_data = WC_WooMercadoPago_Module::get_site_data();
        $this->log = WC_WooMercadoPago_Log::init_mercado_pago_log();
        $this->mp = WC_WooMercadoPago_Module::getMpInstanceSingleton();
    }

    /**
     * @return string
     */
    public function getMpLogo()
    {
        return '<img width="200" height="52" src="' . plugins_url('../assets/images/mplogo.png', plugin_dir_path(__FILE__)) . '"><br><br>';
    }

    /**
     * @return mixed
     */
    public function getMpIcon()
    {
        return apply_filters('woocommerce_mercadopago_icon', plugins_url('../assets/images/mercadopago.png', plugin_dir_path(__FILE__)));
    }

    /**
     * @param $description
     * @return string
     */
    public function getMethodDescription($description)
    {
        return '<img width="200" height="52" src="' . plugins_url('../assets/images/mplogo.png', plugin_dir_path(__FILE__)) . '"><br><br><strong>' . __($description, 'woocommerce-mercadopago') . '</strong>';
    }

    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        $this->init_form_fields();
        $this->init_settings();
        $_site_id_v1 = get_option('_site_id_v1', '');
        $form_fields = array();
        if (empty($_site_id_v1)) {
            $form_fields['no_credentials_title'] = $this->field_no_credentials();
            return $form_fields;
        }

        $form_fields['enabled'] = $this->field_enabled($label);
        if (empty($this->settings['enabled']) || 'no' == $this->settings['enabled']) {
            $form_fields_enable = array();
            $form_fields_enable['enabled'] = $form_fields['enabled'];
            return $form_fields_enable;
        }
        $form_fields['checkout_options_title'] = $this->field_checkout_options_title();
        $form_fields['description'] = $this->field_description();
        $form_fields['payment_title'] = $this->field_payment_title();
        $form_fields['binary_mode'] = $this->field_binary_mode();
        $form_fields['gateway_discount'] = $this->field_gateway_discount();

        return $form_fields;
    }

    /**
     * @param $formFields
     * @param $ordenation
     * @return array
     */
    public function sortFormFields($formFields, $ordenation)
    {
        $array = array();
        foreach ($ordenation as $order => $key) {
            $array[$key] = $formFields[$key];
            unset($formFields[$key]);
        }
        return array_merge_recursive($array, $formFields);
    }

    /**
     * @return array
     */
    public function field_no_credentials()
    {
        $noCredentials = array(
            'title' => sprintf(__('It appears that your credentials are not properly configured.<br/>Please, go to %s and configure it.', 'woocommerce-mercadopago'),
                '<a href="' . esc_url(admin_url('admin.php?page=mercado-pago-settings')) . '">' . __('Mercado Pago Settings', 'woocommerce-mercadopago') .
                '</a>'
            ),
            'type' => 'title');
        return $noCredentials;
    }

    /**
     * @param $label
     * @return array
     */
    public function field_enabled($label)
    {
        $enabled = array(
            'title' => __('Enable/Disable', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Enable ' . $label . ' Checkout', 'woocommerce-mercadopago'),
            'default' => 'no'
        );
        return $enabled;
    }

    /**
     * @return array
     */
    public function field_checkout_options_title()
    {
        $checkout_options_title = array(
            'title' => __('Checkout Interface: How checkout is shown', 'woocommerce-mercadopago'),
            'type' => 'title'
        );
        return $checkout_options_title;
    }

    /**
     * @return array
     */
    public function field_title()
    {
        $title = array(
            'title' => __('Title', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => __('Title shown to the client in the checkout.', 'woocommerce-mercadopago'),
            'default' => __('Mercado Pago', 'woocommerce-mercadopago')
        );

        return $title;
    }

    /**
     * @return array
     */
    public function field_description()
    {
        $description = array(
            'title' => __('Description', 'woocommerce-mercadopago'),
            'type' => 'textarea',
            'description' => __('Description shown to the client in the checkout.', 'woocommerce-mercadopago'),
            'default' => __('Pay with Mercado Pago', 'woocommerce-mercadopago')
        );
        return $description;
    }

    /**
     * @return array
     */
    public function field_payment_title()
    {
        $payment_title = array(
            'title' => __('Payment Options: How payment options behaves', 'woocommerce-mercadopago'),
            'type' => 'title'
        );
        return $payment_title;
    }

    /**
     * @return array
     */
    public function field_binary_mode()
    {
        $binary_mode = array(
            'title' => __('Binary Mode', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Enable binary mode for checkout status', 'woocommerce-mercadopago'),
            'default' => 'no',
            'description' => __('When charging a credit card, only [approved] or [reject] status will be taken.', 'woocommerce-mercadopago')
        );
        return $binary_mode;
    }

    /**
     * @return array
     */
    public function field_gateway_discount()
    {
        $gateway_discount = array(
            'title' => __('Discount/Fee by Gateway', 'woocommerce-mercadopago'),
            'type' => 'number',
            'description' => __('Give a percentual (-99 to 99) discount or fee for your customers if they use this payment gateway. Use negative for fees, positive for discounts.', 'woocommerce-mercadopago'),
            'default' => '0',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '-99',
                'max' => '99'
            )
        );
        return $gateway_discount;
    }

    /**
     * Mensage credentials not configured.
     *
     * @return string Error Mensage.
     */
    public function credential_missing_message()
    {
        echo '<div class="error"><p><strong> Mercado Pago: </strong>' . sprintf(__('It appears that your credentials are not properly configured.<br/>Please, go to %s and configure it.', 'woocommerce-mercadopago'), '<a href="' . esc_url(admin_url('admin.php?page=mercado-pago-settings')) . '">' . __('Mercado Pago Settings', 'woocommerce-mercadopago') . '</a>') . '</p></div>';

    }


    /**
     * @return bool
     */
    public function is_available()
    {
        if (!did_action('wp_loaded')) {
            return false;
        }
        global $woocommerce;
        $w_cart = $woocommerce->cart;
        // Check for recurrent product checkout.
        if (isset($w_cart)) {
            if (WC_WooMercadoPago_Module::is_subscription($w_cart->get_cart())) {
                return false;
            }
        }

        $_mp_public_key = get_option('_mp_public_key');
        $_mp_access_token = get_option('_mp_access_token');
        $_site_id_v1 = get_option('_site_id_v1');

        return ( 'yes' == $this->settings['enabled'] ) && !empty( $_mp_public_key ) && ! empty( $_mp_access_token ) && ! empty( $_site_id_v1 );
    }


    /**
     * @return mixed
     */
    public function admin_url()
    {
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '>=')) {
            return admin_url('admin.php?page=wc-settings&tab=checkout&section=' . $this->id);
        }
        return admin_url('admin.php?page=woocommerce_settings&tab=payment_gateways&section=' . get_class($this));
    }
}