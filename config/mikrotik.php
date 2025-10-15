<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default MikroTik Settings
    |--------------------------------------------------------------------------
    |
    | These settings are used as fallback when no specific MikroTik settings
    | are provided. They can be overridden by tenant-specific settings.
    |
    */

    'default' => [
        'host' => env('MIKROTIK_HOST', '192.168.88.1'),
        'username' => env('MIKROTIK_USERNAME', 'admin'),
        'password' => env('MIKROTIK_PASSWORD', ''),
        'port' => (int) env('MIKROTIK_PORT', 8728),
        'ssl' => (bool) env('MIKROTIK_SSL', false),
        'timeout' => (int) env('MIKROTIK_TIMEOUT', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | RADIUS Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are used for RADIUS authentication between MikroTik
    | routers and the application.
    |
    */

    'radius' => [
        'timeout' => env('RADIUS_TIMEOUT', '5s'),
        'retry' => (int) env('RADIUS_RETRY', 3),
        'secret' => env('RADIUS_SECRET', env('APP_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control how MikroTik sync jobs are queued.
    |
    */

    'queue' => [
        'name' => env('MIKROTIK_QUEUE', 'mikrotik'),
        'connection' => env('MIKROTIK_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'retry_after' => (int) env('MIKROTIK_QUEUE_RETRY_AFTER', 90),
        'tries' => (int) env('MIKROTIK_QUEUE_TRIES', 3),
        'backoff' => [
            (int) env('MIKROTIK_QUEUE_BACKOFF_1', 60),    // 1 minute
            (int) env('MIKROTIK_QUEUE_BACKOFF_2', 300),   // 5 minutes
            (int) env('MIKROTIK_QUEUE_BACKOFF_3', 900),   // 15 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Defaults
    |--------------------------------------------------------------------------
    |
    | Default values for new network users.
    |
    */

    'user_defaults' => [
        'status' => 'active',
        'expires_after_days' => 30, // Default expiration period in days
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for MikroTik operations.
    |
    */

    'logging' => [
        'enabled' => (bool) env('MIKROTIK_LOGGING_ENABLED', true),
        'level' => env('MIKROTIK_LOGGING_LEVEL', 'debug'),
        'channel' => env('MIKROTIK_LOGGING_CHANNEL', 'stack'),
    ],
];
