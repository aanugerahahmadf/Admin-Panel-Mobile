<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini menghubungkan aplikasi Anda dengan dashboard Midtrans.
    | Pastikan Server Key dan Client Key sesuai dengan environment (Sandbox/Production).
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // Set ke false untuk Sandbox (Development), true untuk Production (Live)
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),

    // Rekomendasi Midtrans: Set true untuk pembersihan input
    'is_sanitized' => filter_var(env('MIDTRANS_IS_SANITIZED', true), FILTER_VALIDATE_BOOLEAN),

    // Wajib true untuk kartu kredit (3D Secure)
    'is_3ds' => filter_var(env('MIDTRANS_IS_3DS', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Snap URL
    |--------------------------------------------------------------------------
    |
    | URL Script Snap yang akan dipanggil di frontend (blade).
    |
    */
    'snap_url' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |--------------------------------------------------------------------------
    | Snap-BI Configuration (Standard Nasional Open API Pembayaran)
    |--------------------------------------------------------------------------
    */
    'snap_bi' => [
        'client_id' => env('MIDTRANS_SNAP_BI_CLIENT_ID', ''),
        'private_key' => env('MIDTRANS_SNAP_BI_PRIVATE_KEY', ''),
        'client_secret' => env('MIDTRANS_SNAP_BI_CLIENT_SECRET', ''),
        'partner_id' => env('MIDTRANS_SNAP_BI_PARTNER_ID', ''),
        'channel_id' => env('MIDTRANS_SNAP_BI_CHANNEL_ID', ''),
        'public_key' => env('MIDTRANS_SNAP_BI_PUBLIC_KEY', ''),
    ],
];
