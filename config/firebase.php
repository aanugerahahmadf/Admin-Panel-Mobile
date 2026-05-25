<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    'credentials' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),

    'features' => [
        'realtime_database' => true,
        'firestore' => false,
        'authentication' => true,
        'storage' => true,
        'messaging' => true,
    ],

    'cache' => [
        'ttl' => env('FIREBASE_CACHE_TTL', 3600),
    ],
];
