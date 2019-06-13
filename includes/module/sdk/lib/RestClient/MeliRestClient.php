<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class MeliRestClient
 */
class MeliRestClient extends AbstractRestClient
{
    const API_BASE_URL = 'https://api.mercadolibre.com';

    /**
     * @param $request
     * @param $version
     * @return false|resource
     * @throws WC_WooMercadoPago_Exception
     */
    public static function requestApi($request, $version)
    {
        return self::build_request($request, $version, self::API_BASE_URL);
    }

    public static function exec($request, $version)
    {
        $connect = self::build_request($request, $version, self::API_BASE_URL);
        return self::execute($request,$version, $connect);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     */
    public static function get($request, $version)
    {
        $request['method'] = 'GET';

        return self::exec($request, $version);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     */
    public static function post($request, $version)
    {
        $request['method'] = 'POST';

        return self::exec($request, $version);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     */
    public static function put($request, $version)
    {
        $request['method'] = 'PUT';

        return self::exec($request, $version);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     */
    public static function delete($request, $version)
    {
        $request['method'] = 'DELETE';

        return self::exec($request, $version);
    }


}