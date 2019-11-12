<?php

/**
 * Class WC_WooMercadoPago_Helpers_CurrencyConverter
 */
class WC_WooMercadoPago_Helpers_CurrencyConverter
{
    const CONFIG_KEY    = 'currency_conversion';
    const DEFAULT_RATIO = 1;
    private static $instance;
    private $ratios = [];
    private $cache = [];
    private $currencyCache = [];
    private $supportedCurrencies;
    private $isShowingAlert = false;

    /**
     * Private constructor to make class singleton
     */
    private function __construct()
    {
        return $this;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return $this
     */
    private function init(WC_WooMercadoPago_PaymentAbstract $method)
    {
        if (!isset($this->ratios[$method->id])) {
            try {
                if (!$this->isEnabled($method)) {
                    $this->setRatio($method->id);
                    return $this;
                }

                $accountCurrency = $this->getAccountCurrency($method);
                $localCurrency = get_woocommerce_currency();

                if (!$accountCurrency || $accountCurrency == $localCurrency) {
                    $this->setRatio($method->id);
                    return $this;
                }

                $this->setRatio($method->id, $this->loadRatio($localCurrency, $accountCurrency));
            } catch (Exception $e) {
                $this->setRatio($method->id);
            }
        }

        return $this;
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return mixed|null
     */
    private function getAccountCurrency(WC_WooMercadoPago_PaymentAbstract $method)
    {
        $key = $method->id;

        if (isset($this->currencyCache[$key])) {
            return $this->currencyCache[$key];
        }

        $siteId = $this->getSiteId($this->getAccessToken($method));

        if (!$siteId) {
            return null;
        }

        $configs = $this->getCountryConfigs();

        if (!isset($configs[$siteId]) || !isset($configs[$siteId]['currency'])) {
            return null;
        }

        return isset($configs[$siteId]) ? $configs[$siteId]['currency'] : null;
    }

    /**
     * @return array
     */
    private function getCountryConfigs()
    {
        try {
            $configInstance = new WC_WooMercadoPago_Configs();
            return $configInstance->getCountryConfigs();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return mixed
     */
    private function getAccessToken(WC_WooMercadoPago_PaymentAbstract $method)
    {
        $type = $method->getOption('checkout_credential_production') == 'no'
            ? '_mp_access_token_test'
            : '_mp_access_token_prod';

        return $method->getOption($type);
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return mixed
     */
    public function isEnabled(WC_WooMercadoPago_PaymentAbstract $method)
    {
        return $method->getoption(self::CONFIG_KEY, 'no') == 'yes' ? true : false;
    }

    /**
     * @param $methodId
     * @param int $value
     */
    private function setRatio($methodId, $value = self::DEFAULT_RATIO)
    {
        $this->ratios[$methodId] = $value;
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return int|mixed
     */
    private function getRatio(WC_WooMercadoPago_PaymentAbstract $method)
    {
        $this->init($method);
        return isset($this->ratios[$method->id])
            ? $this->ratios[$method->id]
            : self::DEFAULT_RATIO;
    }

    /**
     * @param $fromCurrency
     * @param $toCurrency
     * @return int
     */
    public function loadRatio($fromCurrency, $toCurrency)
    {
        $cacheKey = $fromCurrency . '--' . $toCurrency;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $ratio = self::DEFAULT_RATIO;

        try {
            $result = MeliRestClient::get(
                ['uri' => sprintf('/currency_conversions/search?from=%s&to=%s', $fromCurrency, $toCurrency)]
            );

            if (isset($result['response'], $result['response']['ratio'])) {
                $ratio = $result['response']['ratio'] > 0 ? $result['response']['ratio'] : self::DEFAULT_RATIO;
            }
        } catch (Exception $e) {
            //error getting from API
        }

        $this->cache[$cacheKey] = $ratio;
        return $ratio;
    }

    /**
     * @param $accessToken
     * @return string | null
     */
    private function getSiteId($accessToken)
    {
        try {
            $mp = new MP($accessToken);
            $result = $mp->get(sprintf('/users/me?access_token=%s', $accessToken));
            return isset($result['response'], $result['response']['site_id']) ? $result['response']['site_id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return float
     */
    public function ratio(WC_WooMercadoPago_PaymentAbstract $method)
    {
        $this->init($method);
        return $this->getRatio($method);
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return string|void
     */
    public function getDescription(WC_WooMercadoPago_PaymentAbstract $method)
    {
        return __('Activa esta opción para que el valor de la moneda configurada en WooCommerce sea compatible al valor de la moneda que usas en Mercado Pago');
    }

    /**
     * Check if currency is supported in mercado pago API
     * @param $currency
     * @return bool
     */
    private function isCurrencySupported($currency)
    {
        foreach ($this->getSupportedCurrencies() as $country) {
            if ($country['id'] == $currency) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get supported currencies from mercado pago API
     * @return array|bool
     */
    public function getSupportedCurrencies()
    {
        if (is_null($this->supportedCurrencies)) {
            try {
                $result = MeliRestClient::get(['uri' => '/currencies']);

                if (!isset($result['response'])) {
                    return false;
                }

                $this->supportedCurrencies = $result['response'];
            } catch (Exception $e) {
                $this->supportedCurrencies = [];
            }
        }

        return $this->supportedCurrencies;
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return array
     */
    public function getField(WC_WooMercadoPago_PaymentAbstract $method)
    {
        return array(
            'title'       => __('Convert Currency', 'woocommerce-mercadopago'),
            'type'        => 'select',
            'default'     => 'no',
            'description' => $this->getDescription($method),
            'options'     => array(
                'no'  => __('No', 'woocommerce-mercadopago'),
                'yes' => __('Yes', 'woocommerce-mercadopago'),
            ),
        );
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @param $oldData
     * @param $newData
     */
    public function scheduleNotice(WC_WooMercadoPago_PaymentAbstract $method, $oldData, $newData)
    {
        if ($oldData[self::CONFIG_KEY] != $newData[self::CONFIG_KEY]) {
            $_SESSION[self::CONFIG_KEY]['notice'] = array(
                'type'   => $newData[self::CONFIG_KEY] == 'yes' ? 'enabled' : 'disabled',
                'method' => $method,
            );
        }
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     */
    public function notices(WC_WooMercadoPago_PaymentAbstract $method)
    {
        $show = isset($_SESSION[self::CONFIG_KEY]) ? $_SESSION[self::CONFIG_KEY] : array();
        $localCurrency = get_woocommerce_currency();

        if (isset($show['notice'])) {
            unset($_SESSION[self::CONFIG_KEY]['notice']);
            if ($show['notice']['type'] == 'enabled') {
                echo $this->noticeEnabled($method);
            } elseif ($show['notice']['type'] == 'disabled') {
                echo $this->noticeDisabled($method);
            }
        }

        if (!$this->isEnabled($method) && $localCurrency != $this->getAccountCurrency($method) && !$this->isShowingAlert) {
            echo $this->noticeWarning($method);
        }
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return string
     */
    public function noticeEnabled(WC_WooMercadoPago_PaymentAbstract $method)
    {
        return '
            <div class="notice notice-success">
                <p>' . __(sprintf('Ahora convertimos tu moneda de [%s] a [%s]', get_woocommerce_currency(),
                $this->getAccountCurrency($method))) . '</p>
            </div>
        ';
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return string
     */
    public function noticeDisabled(WC_WooMercadoPago_PaymentAbstract $method)
    {
        return '
            <div class="notice notice-error">
                <p>' . __(sprintf('Dejamos de convertir tu moneda de [%s] a [%s]', get_woocommerce_currency(),
                $this->getAccountCurrency($method))) . '</p>
            </div>
        ';
    }

    /**
     * @param WC_WooMercadoPago_PaymentAbstract $method
     * @return string
     */
    public function noticeWarning(WC_WooMercadoPago_PaymentAbstract $method)
    {
        global $current_section;

        if (in_array($current_section, array($method->id, sanitize_title(get_class($method))), true)) {
            $this->isShowingAlert = true;

            return '
                <div class="notice notice-error">
                    <p>' . __('<b>Atención:</b> revisa la conversión de moneda ya que la configuración que tienes en WooCommerce '
                    . 'no es compatible a la moneda que usas en tu cuenta de Mercado Pago') . '</p>
                </div>
            ';
        }

        return '';
    }
}
