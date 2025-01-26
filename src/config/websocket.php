<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebSocket Server Configuration
    |--------------------------------------------------------------------------
    |
    | Basic server settings. Leave host and port null for auto-detection.
    | SSL settings are optional for secure connections.
    |
    */
    
    'server' => [
        'host' => env('WEBSOCKET_HOST', '127.0.0.1'),
        'port' => env('WEBSOCKET_PORT', 8080),
        'enabled' => env('WEBSOCKET_ENABLED', true),
        'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 1000),
        'ping_interval' => env('WEBSOCKET_PING_INTERVAL', 30),
        'debug' => env('WEBSOCKET_DEBUG', false),
        'ssl' => [
            'enabled' => env('WEBSOCKET_SSL', false),
            'cert_file' => env('WEBSOCKET_SSL_CERT', null),
            'key_file' => env('WEBSOCKET_SSL_KEY', null),
            'verify_peer' => env('WEBSOCKET_SSL_VERIFY', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Configure authentication features.
    |
    */
    
    'authentication' => [
        'enabled' => env('WEBSOCKET_AUTH_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security features and restrictions.
    |
    */
    
    'security' => [
        'ssl_cert_path' => env('WEBSOCKET_SSL_CERT_PATH', null),
        'ssl_key_path' => env('WEBSOCKET_SSL_KEY_PATH', null),
        'allowed_origins' => env('WEBSOCKET_ALLOWED_ORIGINS', '*'),
        'allowed_ips' => env('WEBSOCKET_ALLOWED_IPS', '*'),
        'blocked_ips' => env('WEBSOCKET_BLOCKED_IPS', ''),
        'max_message_size' => env('WEBSOCKET_MAX_MESSAGE_SIZE', 65536),
        'rate_limiting' => [
            'enabled' => env('WEBSOCKET_RATE_LIMIT', false),
            'max_requests' => env('WEBSOCKET_RATE_MAX', 100),
            'window_seconds' => env('WEBSOCKET_RATE_WINDOW', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior and storage.
    |
    */
    
    'logging' => [
        'channel' => env('WEBSOCKET_LOG_CHANNEL', 'daily'),
        'error_log' => env('WEBSOCKET_ERROR_LOG', storage_path('logs/websocket-errors.log')),
        'enabled' => env('WEBSOCKET_LOGGING', true),
        'level' => env('WEBSOCKET_LOG_LEVEL', 'info'),
        'file' => env('WEBSOCKET_LOG_FILE', storage_path('logs/websocket.log')),
        'separate_files' => env('WEBSOCKET_LOG_SEPARATE', false),
        'max_files' => env('WEBSOCKET_LOG_MAX_FILES', 5),
        'log_connections' => env('WEBSOCKET_LOG_CONNECTIONS', true),
        'log_messages' => env('WEBSOCKET_LOG_MESSAGES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Settings
    |--------------------------------------------------------------------------
    |
    | Configure default events and callbacks.
    |
    */
    
    'events' => [
        'default_events' => [
            'connection.open',
            'connection.close',
            'channel.subscribe',
            'channel.unsubscribe',
            'message.received',
            'message.sent',
            'error',
        ],
        'queue_events' => env('WEBSOCKET_QUEUE_EVENTS', false),
        'async_events' => env('WEBSOCKET_ASYNC_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    |
    | Configure debugging and development features.
    |
    */
    
    'debug' => [
        'enabled' => env('WEBSOCKET_DEBUG', false),
        'verbose' => env('WEBSOCKET_DEBUG_VERBOSE', false),
        'log_frames' => env('WEBSOCKET_DEBUG_FRAMES', false),
        'log_memory' => env('WEBSOCKET_DEBUG_MEMORY', false),
    ],
];
