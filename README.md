# SentixTech WebSocket Package for Laravel

## Overview
A powerful, flexible WebSocket implementation for Laravel applications, enabling real-time communication across various use cases.

## Features
- Dynamic channel creation
- Flexible subscription management
- Generic event broadcasting
- User-specific and global messaging
- Comprehensive error handling
- Detailed logging

## Installation

### Composer Installation
```bash
composer require sentixtech/websocket
```

### Publish Configuration
```bash
php artisan vendor:publish --provider="SentixTech\WebSocket\WebSocketServiceProvider"
```

### Configure WebSocket Server
Edit `config/websocket.php`:
```php
return [
    'host' => env('WEBSOCKET_HOST', 'localhost'),
    'port' => env('WEBSOCKET_PORT', 8080),
    'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 1000),
    'max_frame_size' => env('WEBSOCKET_MAX_FRAME_SIZE', 1024 * 1024), // 1MB
];
```

### Start WebSocket Server
```bash
php artisan websocket:serve
```

## Usage Examples

### Channel Management
```php
// Create channels
WebSocket::createChannel('notifications');
WebSocket::createChannel('user_events');
```

### Subscription
```php
// Subscribe a client to a channel
WebSocket::subscribe($socketResource, 'notifications', $userId);

// Unsubscribe from a channel
WebSocket::unsubscribe($socketResource, 'notifications', $userId);
```

### Broadcasting
```php
// Broadcast to all channel subscribers
WebSocket::broadcast('notifications', [
    'type' => 'alert',
    'message' => 'System maintenance in 10 minutes'
]);

// Broadcast to specific users
WebSocket::broadcast('notifications', $message, [
    'users' => [1, 2, 3] // Only send to these user IDs
]);
```

### Event Emission
```php
// Emit a generic event
WebSocket::emit('user_login', [
    'user_id' => 123,
    'timestamp' => now()
]);
```

### Channel Monitoring
```php
// Get number of channel subscribers
$subscriberCount = WebSocket::subscribers('notifications');
```

## Advanced Usage

### Real-time Notifications
```php
// Send a notification to specific users
WebSocket::broadcast('notifications', [
    'title' => 'New Message',
    'content' => 'You have a new message from John',
    'user_ids' => [5, 10] // Only notify these users
]);
```

### Chat System Integration
```php
// Send a chat message
WebSocket::broadcast('chat_room_1', [
    'sender_id' => Auth::id(),
    'message' => $messageContent
]);
```

## Security
- Supports user-specific channel subscriptions
- Flexible access control
- Comprehensive error logging

## Performance
- Non-blocking socket implementation
- Configurable max clients and frame size
- Efficient channel and user management

## Error Handling
All methods return boolean or integer status:
- `createChannel()`: Returns `true/false`
- `subscribe()`: Returns `true/false`
- `broadcast()`: Returns number of successful broadcasts
- `emit()`: Returns number of successful event emissions

## Troubleshooting
- Check `storage/logs/laravel.log` for WebSocket errors
- Ensure WebSocket server is running
- Verify configuration in `config/websocket.php`

## Requirements
- PHP 7.4+
- Laravel 8.0+
- Sockets extension

## Contributing
Contributions are welcome! Please submit pull requests to the repository.

## License
MIT License

## Support
For issues and support, please open a GitHub issue in the repository.
