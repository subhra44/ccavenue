<?php

namespace Subhra\CCAvenue;

use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $response_string = $this->util->decrypt($encrypted_response_string, $working_key);
        parse_str($response_string, $response);

        return $response;
    }

    /**
     * Function to check the status of the given order number
     * @param $order_number Order number for which the status needs to be checked!
     * @param int $transaction_id transaction reference by ccavenue for which the status needs to be checked!
     */
    public function getOrderDetails($order_number, $transaction_id = 0)
    {
        $merchant_id = config('ccavenue.merchant_id');
        $access_code = config('ccavenue.access_code');
        $working_key = config('ccavenue.working_key');

        $merchant_data = [];
        $response_string = '';
        $order_data = [];

        if ($transaction_id) {
            $merchant_data['reference_no'] = $transaction_id;
        } else if ($order_number) {
            $merchant_data['order_no'] = $order_number;
        }

        // dd($merchant_data);
        if ($merchant_data) {
            // dd(json_encode($merchant_data));
            $encRequest = $this->util->encrypt(json_encode($merchant_data), $working_key);

            $order_status_params = [
                'enc_request' => $encRequest,
                'access_code' => $access_code,
                'request_type' => "JSON",
                'response_type' => "JSON",
                'command' => "orderStatusTracker",
                'version' => "1.2"
            ];
            // dd($order_status_params);

            $request_parameters = http_build_query($order_status_params);

            try {
                //making request to to CCAvenue server with the prepared parameters
                // dd($this->util->getAPIEndPoint() . $request_parameters);
                $response = Http::post($this->util->getAPIEndPoint() . $request_parameters)
                    ->getBody()
                    ->getContents();
                // $response = Http::post($this->util->getAPIEndPoint(), $order_status_params)
                //     ->getBody()
                //     ->getContents();
                // dd($response);
                //ccavenue reseponsds with a serialized url response which should be parsed
                // $response = json_decode($response);
                parse_str($response, $order_data);
                // dd($order_data);

                return $this->getOrderStatus($order_data);
            } catch (BadResponseException $e) {
                dd("Error occured" . $e->getMessage());
            } catch (ConnectException $e) { // Wrong URL pinged or server not responding
                dd("Error occured" . $e->getMessage());
            } catch (ClientException $e) { // URL Response error
                dd("Error occured" . $e->getMessage());
            }

            return false;
        }
    }


    /**
     * @param array $parsedData
     */
    private function getOrderStatus($parsedData = [])
    {
        try {
            $working_key = config('ccavenue.working_key');
            $decrypted_response = $this->util->decrypt(str_replace(["\n", "\r"], '', $parsedData['enc_response']), $working_key);
        } catch (Exception $e) {
            // Log.info(["Exception while decrepting the enc_response", $e]);
            return false;
        }

        $order = json_decode($decrypted_response, TRUE);

        return $order;
    }
}
