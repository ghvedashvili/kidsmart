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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
    ],

    'vapid' => [
        'public_key'  => env('VAPID_PUBLIC_KEY', 'BO95-T7kjKahylgHWfnBospqy3dkFl9O6UXfkmCEpruH05easweV2XSKB47VP3zeZwzz91YoUSjWok_MSWioafQ'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'HXwrL2xH5j7jnXx9C0ttRzl-IvPATF6DvT905LKxXrk'),
        'subject'     => env('APP_URL', 'https://kidsmart.laravel.cloud'),
    ],

];
