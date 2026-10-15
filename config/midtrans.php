<?php

return [
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'options' => [
        CURLOPT_CONNECTTIMEOUT => env('MIDTRANS_CONNECT_TIMEOUT', 30),
        CURLOPT_TIMEOUT => env('MIDTRANS_TIMEOUT', 60),
    ],
    'finish_url' => env('MIDTRANS_FINISH_URL'),
    'unfinish_url' => env('MIDTRANS_UNFINISH_URL'),
    'error_url' => env('MIDTRANS_ERROR_URL'),
];
