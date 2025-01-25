# Free WebSocket Server for PHP

A powerful, easy-to-use WebSocket server implementation for PHP applications. Works with any server that supports PHP (Apache, XAMPP, cPanel, etc.).

## Features

✨ **Zero Dependencies** - Pure PHP implementation, no external packages required
🚀 **Auto Configuration** - Automatic host, port, and SSL detection
🔒 **Secure** - Built-in SSL/TLS support
🎯 **Custom Channels** - Create and manage multiple channels
🔥 **Event System** - Built-in event system with custom event support
⚡ **High Performance** - Optimized for handling multiple connections
📝 **Easy Integration** - Works with any PHP framework

## Installation

```bash
composer require sentixtech/websoket
```

## Quick Start

### 1. Server Setup (PHP)

```php
<?php
use Sentixtech\Websoket\WebsoketServer;

$server = new WebsoketServer();

// Create a channel
$server->createChannel('chat', [
    'maxClients' => 100,
    'private' => false
]);

// Handle messages
$server->on('message.received', function($data, $socket) use ($server) {
    $server->broadcast($data, 'chat');
});

// Start server
$server->start();
```

### 2. Client Setup (JavaScript)

```javascript
// Auto-detect protocol (ws:// or wss://)
const protocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
const ws = new WebSocket(`${protocol}${window.location.host}:8080`);

// Subscribe to channel
ws.onopen = () => {
    ws.send(JSON.stringify({
        type: 'subscribe',
        channel: 'chat'
    }));
};

// Send message
function sendMessage(message) {
    ws.send(JSON.stringify({
        type: 'publish',
        channel: 'chat',
        message: message
    }));
}

// Receive messages
ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('Received:', data);
};
```

## Configuration

### Environment Variables (.env)

```env
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080
WEBSOCKET_SSL=false
WEBSOCKET_MAX_CLIENTS=1000
WEBSOCKET_DEBUG=false
```

### Custom Configuration

```php
$server = new WebsoketServer();

$server->setMaxClients(500)
       ->setPingInterval(30)
       ->enableDebug();
```

## Events

### Built-in Events
- `connection.open` - New client connected
- `connection.close` - Client disconnected
- `channel.subscribe` - Client subscribed to channel
- `channel.unsubscribe` - Client unsubscribed from channel
- `message.received` - Message received from client
- `message.sent` - Message sent to client
- `error` - Error occurred

### Custom Events

```php
// Server-side
$server->on('custom.event', function($data, $socket) {
    echo "Custom event: " . json_encode($data);
});

// Client-side
ws.send(JSON.stringify({
    type: 'event',
    name: 'custom.event',
    data: { foo: 'bar' }
}));
```

## Channel Features

### Public Channels
```php
$server->createChannel('public', [
    'maxClients' => 0, // unlimited
    'persistent' => true
]);
```

### Private Channels
```php
$server->createChannel('private-chat', [
    'private' => true,
    'maxClients' => 2
]);
```

### Broadcasting
```php
// Broadcast to specific channel
$server->broadcast($message, 'channel-name');

// Broadcast to all channels
$server->broadcast($message);
```

## Security Features

- SSL/TLS Support
- IP Whitelisting/Blacklisting
- Rate Limiting
- Message Size Limits
- Origin Restrictions

## Performance Optimization

- Connection Pooling
- Memory Management
- Buffer Size Control
- Client Limits per IP

## Example Use Cases

1. Real-time Chat
2. Live Notifications
3. Game Updates
4. Analytics Dashboard
5. Collaborative Editing
6. IoT Device Monitoring
7. Live Sports Updates
8. Stock Market Tickers

## Browser Support

- Chrome 6+
- Firefox 6+
- Safari 7+
- Opera 12.1+
- IE 10+
- Edge All versions

## License

MIT License - feel free to use in commercial projects!

## Support

Need help? Create an issue on GitHub or contact us at support@sentixtech.com
