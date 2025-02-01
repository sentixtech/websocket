<?php

return [
    'host' => env('WEBSOCKET_HOST', 'localhost'),
    'port' => env('WEBSOCKET_PORT', 8080),
    'allowed_origins' => env('WEBSOCKET_ALLOWED_ORIGINS', '*'),
    'max_connections' => env('WEBSOCKET_MAX_CONNECTIONS', 1000),
    'ping_interval' => env('WEBSOCKET_PING_INTERVAL', 30),
    
    // Broadcast configuration
    'broadcast' => [
        'driver' => env('WEBSOCKET_BROADCAST_DRIVER', 'local'),
        'channels' => [
            'default' => [
                'name' => 'default',
                'type' => 'public', // public, private, presence
            ],
        ],
    ],
];
