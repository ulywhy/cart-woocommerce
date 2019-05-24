<?php
/**
 * Class MeliRestClient
 */
class MeliRestClient extends AbstractRestClient
{
    public function __construct()
    {
        parent::setApiBaseUrl('https://api.mercadolibre.com');
    }
}