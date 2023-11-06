<?php

return [
    /*
     * CCAvenue merchant id
     */
    'merchant_id'    => env('CCAVENUE_MERCHANT_ID'),

    /*
     * CCAvenue access code
     */
    'access_code' => env('CCAVENUE_ACCESS_CODE'),

    /*
     * CCAvenue working key
     */
    'working_key' => env('CCAVENUE_WORKING_KEY'),

    /*
     * CCAvenue environment
     */
    'production'            => env('CCAVENUE_PRODUCTION', false),

    /*
     * ISO code for the currency
     */
    'currency'        => env('CCAVENUE_CURRENCY', 'INR'),

    /*
     * Route name to handle the redirect
     */
    'redirect_route'  => env('CCAVENUE_REDIRECT_ROUTE'),

    /*
     * CCAvenue billing page language
     */
    'language' => env('CCAVENUE_LANGUAGE'),

];
