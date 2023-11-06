<?php

namespace Subhra\CCAvenue;

use Subhra\CCAvenue\Utils;

class CCAvenue
{
    protected $util;

    public function __construct($environment = 'test')
    {
        if (config('ccavenue.production'))
            $environment = 'production';

        $this->util = new Utils($environment);
    }

    public function initiateTransaction($orderData)
    {
        $merchant_id = config('ccavenue.merchant_id');
        $access_code = config('ccavenue.access_code');
        $working_key = config('ccavenue.working_key');
        $currency = config('ccavenue.currency');
        $language = config('ccavenue.language');
        $redirect_url = route(config('ccavenue.redirect_route'));

        $order = $orderData;
        $order['merchant_id'] = $merchant_id;
        $order['currency'] = $currency;
        $order['redirect_url'] = $redirect_url;
        $order['cancel_url'] = $redirect_url;
        $order['integration_type'] = 'iframe_normal';
        $order['language'] = $language;
        // dd($order);

        $iframe_src = $this->util->getMerchertOrderURL($order, $access_code, $working_key);

        return '<iframe src="' . $iframe_src . '" width="100%" height="100%" scrolling="auto" frameBorder="0"> <p>Unable to load the payment page</p> </iframe>';
    }

    public function parseResponse($encrypted_response)
    {
        $working_key = config('ccavenue.working_key');
        $encrypted_response_string = $encrypted_response["encResp"];

        $resonse_string = $this->util->decrypt($encrypted_response_string, $working_key);
        $resonse = explode('&', $resonse_string);

        return $resonse;
    }
}
