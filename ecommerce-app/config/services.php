<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

   
 
   
    'paytech' => [
        'api_key' => env('PAYTECH_API_KEY'),
        'secret_key' => env('PAYTECH_API_SECRET'), // Utilise PAYTECH_API_SECRET du .env
        'base_url' => env('PAYTECH_BASE_URL', 'https://paytech.sn/api'),
        'notify_url' => env('PAYTECH_IPN_URL', env('APP_URL') . '/payment/notify'),
        'return_url' => env('PAYTECH_SUCCESS_URL', env('APP_URL') . '/payment/verify'),
        'cancel_url' => env('PAYTECH_CANCEL_URL', env('APP_URL') . '/payment/cancel'),
        'environment' => env('PAYTECH_ENV', 'test'), // Utilise PAYTECH_ENV du .env
    ],

    'paydunya' => [
        'master_key' => env('PAYDUNYA_MASTER_KEY'), // Clé Publique/Principale
        'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'base_url' => env('PAYDUNYA_BASE_URL', 'https://app.paydunya.com'),
        'notify_url' => env('PAYDUNYA_NOTIFY_URL', env('APP_URL') . '/payment/notify'),
        'return_url' => env('PAYDUNYA_RETURN_URL', env('APP_URL') . '/payment/success'),
        'cancel_url' => env('PAYDUNYA_CANCEL_URL', env('APP_URL') . '/payment/cancel'),
        'environment' => env('PAYDUNYA_ENV', 'test'), // test ou prod
    ],

];
