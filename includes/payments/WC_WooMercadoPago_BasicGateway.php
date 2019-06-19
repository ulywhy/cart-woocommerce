<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 *
 * WC_WooMercadoPago_BasicGateway
 *
 */
class WC_WooMercadoPago_BasicGateway extends WC_WooMercadoPago_PaymentAbstract
{

    /**
     * WC_WooMercadoPago_BasicGateway constructor.
     * @throws WC_WooMercadoPago_Exception
     */
    public function __construct()
    {
        $this->id = 'woo-mercado-pago-basic';
        $this->form_fields = array();
        $this->method = $this->get_option('method', 'redirect');
        $this->title = $this->get_option('title', __('Mercado Pago - Basic Checkout', 'woocommerce-mercadopago'));
        $this->auto_return = get_option('auto_return', 'yes');
        $this->success_url = get_option('success_url', '');
        $this->failure_url = get_option('failure_url', '');
        $this->pending_url = get_option('pending_url', '');
        $this->installments = get_option('installments', '24');
        $this->gateway_discount = get_option('gateway_discount', 0);
        $this->field_forms_order = $this->get_fields_sequence();
        $this->ex_payments = $this->getExPayments();
        parent::__construct();
        $this->form_fields = $this->getFormFields('Basic');
        $this->two_cards_mode = $this->mp->check_two_cards();
        $this->hook = new WC_WooMercadoPago_Hook_Basic($this);
        $this->notification = new WC_WooMercadoPago_Notification_IPN($this);
    }


    /**
     * @param $label
     * @return array
     */
    public function getFormFields($label)
    {
        $form_fields = array();
        $form_fields['checkout_header'] = $this->field_checkout_header();
        $form_fields['checkout_options_title'] = $this->field_checkout_options_title();
        $form_fields['checkout_options_subtitle'] = $this->field_checkout_options_subtitle();
        $form_fields['checkout_payments_title'] = $this->field_checkout_payments_title();
        $form_fields['checkout_payments_subtitle'] = $this->field_checkout_payments_subtitle();
        $form_fields['checkout_payments_description'] = $this->field_checkout_options_description();
        $form_fields['installments'] = $this->field_installments();
        $form_fields['checkout_payments_advanced_title'] = $this->field_checkout_payments_advanced_title();
        $form_fields['method'] = $this->field_method();
        $form_fields['success_url'] = $this->field_success_url();
        $form_fields['failure_url'] = $this->field_failure_url();
        $form_fields['pending_url'] = $this->field_pending_url();
        $form_fields['auto_return'] = $this->field_auto_return();


        foreach ($this->field_ex_payments() as $key => $value) {
            $form_fields[$key] = $value;
        }

        $form_fields_abs = parent::getFormFields($label);
        if (count($form_fields_abs) == 1) {
            return $form_fields_abs;
        }
        $form_fields_merge = array_merge($form_fields_abs, $form_fields);
        $fields = $this->sortFormFields($form_fields_merge, $this->field_forms_order);

        return $fields;
    }

    /**
     * get_fields_sequence
     *
     * @return array
     */
    public function get_fields_sequence()
    {
        return [
            'checkout_header',
            'checkout_steps',
            'checkout_credential_title',
            'checkout_credential_subtitle',
            'checkout_credential_production',
            '_mp_public_key_test',
            '_mp_access_token_test',
            '_mp_public_key_prod',
            '_mp_access_token_prod',
            'checkout_credential_description',
            'checkout_options_title',
            'checkout_options_subtitle',
            'description',
            '_mp_category_id',
            '_mp_store_identificator',
            'checkout_advanced_settings',
            '_mp_debug_mode',
            '_mp_custom_domain',
            'checkout_payments_title',
            'checkout_payments_subtitle',
            'checkout_payments_description',
            'enabled',
            'installments',
            'checkout_payments_advanced_title',
            'auto_return',
            'method',
            'success_url',
            'failure_url',
            'pending_url',
            'binary_mode',
            'gateway_discount',
            'checkout_ready_title',
            'checkout_ready_description',
            'checkout_ready_description_link'
        ];
    }

    /**
     * @return bool
     */
    public function is_available()
    {
        return parent::is_available();
    }

    /**
     * @return array
     */
    private function getExPayments()
    {
        $ex_payments = array();
        $get_ex_payment_options = get_option('_all_payment_methods_v0', '');
        if (!empty($get_ex_payment_options)) {
            foreach ($get_ex_payment_options = explode(',', $get_ex_payment_options) as $get_ex_payment_option) {
                if (get_option('ex_payments_' . $get_ex_payment_option, 'yes') == 'no') {
                    $ex_payments[] = $get_ex_payment_option;
                }
            }
        }
        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_checkout_header()
    {
        $checkout_header = array(
            'title' => sprintf(
                __('Checkout Básico. Acepta todos los medios de pago y lleva tus cobros a otro nivel. %s', 'woocommerce-mercadopago'),
                '<div class="row">
              <div class="col-md-12">
                <p class="text-checkout-body mb-0">
                  ' . __('Convierte tu tienda online en la pasarela de pagos preferida de tus clientes. Elige la experiencia de <br> pago final entre las opciones disponibles.') . '
                </p>
              </div>
            </div>'
            ),
            'type' => 'title',
            'class' => 'mp_title_checkout'
        );
        return $checkout_header;
    }

    /**
     * @return array
     */
    public function field_checkout_options_title()
    {
        $checkout_options_title = array(
            'title' => __('Configura WooCommerce Mercado Pago', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_options_title;
    }

    /**
     * @return array
     */
    public function field_checkout_options_subtitle()
    {
        $checkout_options_subtitle = array(
            'title' => __('Ve a lo básico. Coloca la información de tu negocio.', 'woocommerce-mercadopago'),
            'type' => 'title'
        );
        return $checkout_options_subtitle;
    }

    /**
     * @return array
     */
    public function field_checkout_options_description()
    {
        $checkout_options_subtitle = array(
            'title' => __('Habilita Mercado Pago en tu tienda online, selecciona los medios de pago disponibles para tus clientes y <br> define el máximo de cuotas en el que podrán pagarte.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_small_text'
        );
        return $checkout_options_subtitle;
    }


    /**
     * @return array
     */
    public function field_checkout_payments_title()
    {
        $checkout_payments_title = array(
            'title' => __('Configura la experiencia de pago en tu tienda.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_title_bd'
        );
        return $checkout_payments_title;
    }

    /**
     * @return array
     */
    public function field_checkout_payments_subtitle()
    {
        $checkout_payments_subtitle = array(
            'title' => __('Configuración Básica de la experiencia de pago.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle'
        );
        return $checkout_payments_subtitle;
    }

    /**
     * @return array
     */
    public function field_installments()
    {
        $installments = array(
            'title' => __('Máximo de cuotas', 'woocommerce-mercadopago'),
            'type' => 'select',
            'description' => __('¿Cuál es el máximo de cuotas con las que un cliente puede comprar?', 'woocommerce-mercadopago'),
            'default' => '24',
            'options' => array(
                '1' => __('1x installment', 'woocommerce-mercadopago'),
                '2' => __('2x installmens', 'woocommerce-mercadopago'),
                '3' => __('3x installmens', 'woocommerce-mercadopago'),
                '4' => __('4x installmens', 'woocommerce-mercadopago'),
                '5' => __('5x installmens', 'woocommerce-mercadopago'),
                '6' => __('6x installmens', 'woocommerce-mercadopago'),
                '10' => __('10x installmens', 'woocommerce-mercadopago'),
                '12' => __('12x installmens', 'woocommerce-mercadopago'),
                '15' => __('15x installmens', 'woocommerce-mercadopago'),
                '18' => __('18x installmens', 'woocommerce-mercadopago'),
                '24' => __('24x installmens', 'woocommerce-mercadopago')
            )
        );
        return $installments;
    }

    /**
     * @return array
     */
    public function field_checkout_payments_advanced_title()
    {
        $checkout_payments_advanced_title = array(
            'title' => __('Configuración Avanzada de la experiencia de pago.', 'woocommerce-mercadopago'),
            'type' => 'title',
            'class' => 'mp_subtitle_bd'
        );
        return $checkout_payments_advanced_title;
    }

    /**
     * @return array
     */
    public function field_method()
    {
        $method = array(
            'title' => __('Método de integración', 'woocommerce-mercadopago'),
            'type' => 'select',
            'description' => __('Define qué experiencia de pago tendrán tus clientes, si dentro o fuera de tu tienda. Conoce las ventajas y desventajas de cada opción en  nuestra guiás', 'woocommerce-mercadopago'),
            'default' => 'redirect',
            'options' => array(
                'redirect' => __('Redirect', 'woocommerce-mercadopago'),
                'modal' => __('Modal Window', 'woocommerce-mercadopago')
            )
        );
        return $method;
    }

    /**
     * @return array
     */
    public function field_success_url()
    {
        // Validate back URL.
        if (!empty($this->success_url) && filter_var($this->success_url, FILTER_VALIDATE_URL) === FALSE) {
            $success_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $success_back_url_message = __('Where customers should be redirected after a successful purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $success_url = array(
            'title' => __('Sucess URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $success_back_url_message,
            'default' => ''
        );
        return $success_url;
    }

    /**
     * @return array
     */
    public function field_failure_url()
    {
        if (!empty($this->failure_url) && filter_var($this->failure_url, FILTER_VALIDATE_URL) === FALSE) {
            $fail_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $fail_back_url_message = __('Where customers should be redirected after a failed purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $failure_url = array(
            'title' => __('Failure URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $fail_back_url_message,
            'default' => ''
        );
        return $failure_url;
    }

    /**
     * @return array
     */
    public function field_pending_url()
    {
        // Validate back URL.
        if (!empty($this->pending_url) && filter_var($this->pending_url, FILTER_VALIDATE_URL) === FALSE) {
            $pending_back_url_message = '<img width="14" height="14" src="' . plugins_url('assets/images/warning.png', plugin_dir_path(__FILE__)) . '"> ' .
                __('This appears to be an invalid URL.', 'woocommerce-mercadopago') . ' ';
        } else {
            $pending_back_url_message = __('Where customers should be redirected after a pending purchase. Let blank to redirect to the default store order resume page.', 'woocommerce-mercadopago');
        }
        $pending_url = array(
            'title' => __('Pending URL', 'woocommerce-mercadopago'),
            'type' => 'text',
            'description' => $pending_back_url_message,
            'default' => ''
        );
        return $pending_url;
    }

    /**
     * @return array
     */
    public function field_ex_payments()
    {
        $ex_payments = array();
        $ex_payments_sort = array();

        $all_payments = get_option('_checkout_payments_methods', '');
        $get_payment_methods = get_option('_all_payment_methods_v0', '');

        if (!empty($get_payment_methods)) {
            $get_payment_methods = explode(',', $get_payment_methods);
        }

        $count_payment = 0;

        foreach ($get_payment_methods as $payment_method) {
            if ($all_payments[$count_payment]['type'] == 'credit_card' || $all_payments[$count_payment]['type'] == 'debit_card' || $all_payments[$count_payment]['type'] == 'prepaid_card') {
                $element = array(
                    'label' => $all_payments[$count_payment]['name'],
                    'id' => 'woocommerce_mercadopago_' . $payment_method,
                    'default' => 'yes',
                    'type' => 'checkbox',
                    'class' => 'online_payment_method',
                    'custom_attributes' => array(
                        'data-translate' => __('Selecciona pagos online', 'woocommerce-mercadopago') 
                    ),
                );
            }
            else{
                $element = array(
                    'label' => $all_payments[$count_payment]['name'],
                    'id' => 'woocommerce_mercadopago_' . $payment_method,
                    'default' => 'yes',
                    'type' => 'checkbox',
                    'class' => 'offline_payment_method',
                    'custom_attributes' => array(
                        'data-translate' => __('Selecciona pagos offline' , 'woocommerce-mercadopago')
                    ),
                );
            }

            $count_payment++;

            if ($count_payment == 1) {
                $element['title'] = __('Medios de pago', 'woocommerce-mercadopago');
                $element['desc_tip'] = __('Selecciona los medios de pago disponibles en tu tienda.', 'woocommerce-services');
            }
            if ($count_payment == count($get_payment_methods)) {
                $element['description'] = __('Habilita los medios de pago disponibles para tus clientes.', 'woocommerce-mercadopago');
            }

            $ex_payments["ex_payments_" . $payment_method] = $element;
            $ex_payments_sort[] = "ex_payments_" . $payment_method;
        }

        array_splice($this->field_forms_order, 13, 0, $ex_payments_sort);

        return $ex_payments;
    }

    /**
     * @return array
     */
    public function field_auto_return()
    {
        $auto_return = array(
            'title' => __('Volver a la tienda', 'woocommerce-mercadopago'),
            'type' => 'checkbox',
            'label' => __('Si', 'woocommerce-mercadopago'),
            'default' => 'yes',
            'description' => __('¿Quieres que tu cliente vuelva a la tienda después de finalizar la compra?', 'woocommerce-mercadopago'),
        );
        return $auto_return;
    }

    /**
     * Payment Fields
     */
    public function payment_fields()
    {
        //add css
        wp_enqueue_style(
            'woocommerce-mercadopago-basic-checkout-styles',
            plugins_url('../assets/css/basic_checkout_mercadopago.css', plugin_dir_path(__FILE__))
        );

        //validate active payments methods
        $debito = 0;
        $credito = 0;
        $efectivo = 0;
        $tarjetas = get_option('_checkout_payments_methods', '');
        $cho_tarjetas = array();

        foreach ($tarjetas as $tarjeta) {
            if ($this->get_option($tarjeta['config'], '') == 'yes') {
                $cho_tarjetas[] = $tarjeta;
                if ($tarjeta['type'] == 'credit_card') {
                    $credito += 1;
                } elseif ($tarjeta['type'] == 'debit_card' || $tarjeta['type'] == 'prepaid_card') {
                    $debito += 1;
                } else {
                    $efectivo += 1;
                }
            }
        }

        $parameters = array(
            "debito" => $debito,
            "credito" => $credito,
            "efectivo" => $efectivo,
            "tarjetas" => $cho_tarjetas,
            "installments" => $this->get_option('installments', ''),
            "cho_image" => plugins_url('../assets/images/redirect_checkout.png', plugin_dir_path(__FILE__)),
        );

        wc_get_template('basic-checkout/basic-checkout.php', $parameters, 'woo/mercado/pago/module/', WC_WooMercadoPago_Module::get_templates_path());
    }

    /**
     * @param $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        if (method_exists($order, 'update_meta_data')) {
            $order->update_meta_data('_used_gateway', get_class($this));
            $order->save();
        } else {
            update_post_meta($order_id, '_used_gateway', get_class($this));
        }

        if ('redirect' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'customer being redirected to Mercado Pago.');
            return array(
                'result' => 'success',
                'redirect' => $this->create_preference($order)
            );
        } elseif ('modal' == $this->method) {
            $this->log->write_log(__FUNCTION__, 'preparing to render Mercado Pago checkout view.');
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }
    }

    /**
     * @param $order
     * @return bool
     */
    public function create_preference($order)
    {
        $preferencesBasic = new WC_WooMercadoPago_PreferenceBasic($order, $this->ex_payments, $this->installments);
        $preferences = $preferencesBasic->get_preference();
        try {
            $checkout_info = $this->mp->create_preference(json_encode($preferences));
            if ($checkout_info['status'] < 200 || $checkout_info['status'] >= 300) {
                $this->log->write_log(__FUNCTION__, 'mercado pago gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } elseif (is_wp_error($checkout_info)) {
                $this->log->write_log(__FUNCTION__, 'wordpress gave error, payment creation failed with error: ' . $checkout_info['response']['message']);
                return false;
            } else {
                $this->log->write_log(__FUNCTION__, 'payment link generated with success from mercado pago, with structure as follow: ' . json_encode($checkout_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if ($this->sandbox) {
                    return $checkout_info['response']['sandbox_init_point'];
                }
                return $checkout_info['response']['init_point'];
            }
        } catch (WC_WooMercadoPago_Exception $ex) {
            $this->log->write_log(__FUNCTION__, 'payment creation failed with exception: ' . json_encode($ex, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return false;
        }
    }
}
