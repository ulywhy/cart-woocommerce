<?php

/**
 * Class WC_WooMercadoPago_Credentials
 */
class WC_WooMercadoPago_Credentials
{

    CONST TYPE_ACCESS_CLIENT = 'client';
    CONST TYPE_ACCESS_TOKEN = 'token';

    public $payment;
    public $publicKey;
    public $accessToken;
    public $clientId;
    public $clientSecret;
    public $testUser;

    /**
     * WC_WooMercadoPago_Credentials constructor.
     * @param $payment
     */
    public function __construct($payment = null)
    {
        $this->payment = $payment;
        $this->testUser = get_option('_test_user_v1', false);

        $this->publicKey = get_option('_mp_public_key', '');
        $this->accessToken = get_option('_mp_access_token', '');

        $this->clientId = get_option('_mp_client_id');
        $this->clientSecret = get_option('_mp_client_secret');
    }

    /**
     * @return bool|string
     */
    public function validateCredentials()
    {
        if ($this->payment instanceof WC_WooMercadoPago_BasicGateway) {
            if (!$this->tokenIsValid()) {
                if (!$this->clientIsValid()) {
                    return false;
                }
                return self::TYPE_ACCESS_CLIENT;
            }
        }
        return self::TYPE_ACCESS_TOKEN;
    }

    /**
     * @return bool
     */
    public function clientIsValid()
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function tokenIsValid()
    {
        if (empty($this->publicKey) || empty($this->accessToken)) {
            return false;
        }

        if (strpos($this->publicKey, 'APP_USR') === false && strpos($this->publicKey, 'TEST') === false) {
            return false;
        }
        if (strpos($this->accessToken, 'APP_USR') === false && strpos($this->accessToken, 'TEST') === false) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function validate_credentials_v1()
    {
        $public_key = get_option('_mp_public_key');
        $access_token = get_option('_mp_access_token');
            // Pre-validate.
            $is_valid_credentials = true;
        if (empty($public_key) || empty($access_token)) {
            $is_valid_credentials = false;
        }
        if (strpos($public_key, 'APP_USR') === false && strpos($public_key, 'TEST') === false) {
            $is_valid_credentials = false;
        }
        if (strpos($access_token, 'APP_USR') === false && strpos($access_token, 'TEST') === false) {
            $is_valid_credentials = false;
        }
        if ($is_valid_credentials) {
            try {
                $mp_v1 = WC_WooMercadoPago_Module::getMpInstanceSingleton();
                $access_token = $mp_v1->get_access_token();
                $get_request = $mp_v1->get('/users/me?access_token=' . $access_token);
                if (isset($get_request['response']['site_id']) && !empty($public_key)) {
                    update_option('_test_user_v1', in_array('test_user', $get_request['response']['tags']), true);
                    update_option('_site_id_v1', $get_request['response']['site_id'], true);
                    update_option('_collector_id_v1', $get_request['response']['id'], true);

                    // Get available payment methods.
                    $payments = $mp_v1->get('/v1/payment_methods/?access_token=' . $access_token);
                    $payments_response = $payments['response'];

                    $payment_methods_ticket = array();
                    $arr = array();
                    $cho = array();
                    foreach ($payments_response as $payment) {
                        $arr[] = $payment['id'];

                        $cho[] = array(
                            "id" => $payment['id'],
                            "name"=> $payment['name'],
                            "type" => $payment['payment_type_id'],
                            "image" => $payment['secure_thumbnail'],
                            "config" => "ex_payments_" . $payment['id'],
                        );
                    }

                    update_option('_all_payment_methods_v0', implode(',', $arr), true);
                    update_option('_checkout_payments_methods', $cho, true);

                    foreach ($payments_response as $payment) {
                        if (isset($payment['payment_type_id'])) {
                            if (
                                $payment['payment_type_id'] != 'account_money' &&
                                $payment['payment_type_id'] != 'credit_card' &&
                                $payment['payment_type_id'] != 'debit_card' &&
                                $payment['payment_type_id'] != 'prepaid_card' &&
                                $payment['id'] != 'pse'
                            ) {
                                $obj = new stdClass();
                                $obj->id = $payment['id'];
                                $obj->name = $payment['name'];
                                $obj->secure_thumbnail = $payment['secure_thumbnail'];
                                array_push($payment_methods_ticket, $obj);
                            }
                        }
                    }
                    update_option('_all_payment_methods_ticket', json_encode($payment_methods_ticket), true);
                    $currency_ratio = WC_WooMercadoPago_Module::get_conversion_rate(WC_WooMercadoPago_Module::$country_configs[$get_request['response']['site_id']]['currency']);
                    if ($currency_ratio > 0) {
                        update_option('_can_do_currency_conversion_v1', true, true);
                    } else {
                        update_option('_can_do_currency_conversion_v1', false, true);
                    }
                    return true;
                }
            } catch (WC_WooMercadoPago_Exception $e) {
                // TODO: should we handle an exception here?
            }
        }
        update_option('_test_user_v1', '', true);
        update_option('_site_id_v1', '', true);
        update_option('_collector_id_v1', '', true);
        update_option('_all_payment_methods_v0', array(), true);
        update_option('_all_payment_methods_ticket', '[]', true);
        update_option('_can_do_currency_conversion_v1', false, true);
        return false;
    }
}
