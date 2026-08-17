<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Dapatkan Server Key & Client Key dari dashboard Midtrans
    | (Sandbox: https://account.sandbox.midtrans.com, Production: https://dashboard.midtrans.com)
    |
    */

    'enabled' => (bool) env('MIDTRANS_ENABLED', false),

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // true untuk Production, false untuk Sandbox
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'snap_web_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v2/vtweb/'
        : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/',
];