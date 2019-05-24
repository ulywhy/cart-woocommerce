<?php
/**
 * Class MPRestClient
 */
class MPRestClient extends AbstractRestClient
{
    public function __construct()
    {
        parent::setApiBaseUrl('https://api.mercadopago.com');
    }
}