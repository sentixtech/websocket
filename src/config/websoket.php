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
        'host' => env('WEBSOCKET_HOST', null),
        'port' => env('WEBSOCKET_PORT', null),
        'ssl' => [
            'enabled' => env('WEBSOCKET_SSL', false),
            'cert_file' => env('WEBSOCKET_SSL_CERT', null),
            'key_file' => env('WEBSOCKET_SSL_KEY', null),
            'verify_peer' => env('WEBSOCKET_SSL_VERIFY', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure server performance and resource usage.
    |
    */
    
    'performance' => [
        'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 1000),
        'max_connections_per_ip' => env('WEBSOCKET_MAX_CONN_PER_IP', 0), // 0 = unlimited
        'memory_limit' => env('WEBSOCKET_MEMORY_LIMIT', '128M'),
        'buffer_size' => env('WEBSOCKET_BUFFER_SIZE', 8192),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Configure connection behavior and timeouts.
    |
    */
    
    'connection' => [
        'ping_interval' => env('WEBSOCKET_PING_INTERVAL', 30),
        'ping_timeout' => env('WEBSOCKET_PING_TIMEOUT', 60),
        'disconnect_timeout' => env('WEBSOCKET_DISCONNECT_TIMEOUT', 120),
        'throttle' => [
            'enabled' => env('WEBSOCKET_THROTTLE', false),
            'max_requests' => env('WEBSOCKET_THROTTLE_MAX', 100),
            'per_seconds' => env('WEBSOCKET_THROTTLE_PER', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for channels. These can be overridden when creating
    | individual channels.
    |
    */
    
    'channels' => [
        'default_options' => [
            'max_clients' => 0, // 0 means unlimited
            'max_message_size' => 65536, // in bytes
            'message_queue_size' => 100,
            'persistent' => false,
            'private' => false,
        ],
        
        'reserved_names' => [
            'system',
            'admin',
            'private-*',
            'presence-*',
        ],
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
