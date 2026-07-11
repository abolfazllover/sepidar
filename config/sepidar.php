<?php

return [

    /*
    |--------------------------------------------------------------------------
    | اتصال به سپیدar
    |--------------------------------------------------------------------------
    */
    'base_url' => env('SEPIDAR_BASE_URL', 'http://localhost:7373'),
    'username' => env('SEPIDAR_USERNAME'),
    'password' => env('SEPIDAR_PASSWORD'),
    'generation_version' => env('SEPIDAR_GENERATION_VERSION', '101'),
    'device_serial' => env('SEPIDAR_DEVICE_SERIAL'),

    /*
    |--------------------------------------------------------------------------
    | تنظیمات داخلی (نیازی به تغییر دستی نیست)
    |--------------------------------------------------------------------------
    */
    'credentials_path' => env('SEPIDAR_CREDENTIALS_PATH', storage_path('app/sepidar/credentials.json')),
    'legacy_credentials_path' => env('SEPIDAR_LEGACY_JSON_PATH'),
    'timeout' => env('SEPIDAR_TIMEOUT', 30),
    'verify_ssl' => env('SEPIDAR_VERIFY_SSL', false),
    'log_requests' => env('SEPIDAR_LOG_REQUESTS', false),
    'log_channel' => env('SEPIDAR_LOG_CHANNEL', 'stack'),

];
