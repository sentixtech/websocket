# Sentixtech WebSocket Server

## 🚀 Overview

A powerful, flexible, and easy-to-use WebSocket server implementation for PHP applications. This package provides a robust solution for real-time communication, supporting various use cases from simple chat applications to complex real-time systems.

## ✨ Key Features

- 🔌 Pure PHP WebSocket Implementation
- 🛡️ Secure Connection Handling
- 📡 Flexible Channel Management
- 🎉 Event-Driven Architecture
- 🔒 Authentication Support
- 📊 Performance Optimized

## 📦 Installation

### Composer Installation

```bash
composer require sentixtech/websocket
```

### Composer Configuration

Add to your `composer.json`:

```json
{
    "require": {
        "sentixtech/websocket": "^1.0"
    }
}
```

## 🛠️ Basic Setup

### Server Initialization

```php
<?php
use Sentixtech\Websocket\WebsocketServer;

// Create WebSocket Server
$server = new WebsocketServer('0.0.0.0', 8080);

// Start the server
$server->start();
```

## 🌐 Channel Management

### Creating Channels

```php
// Public Channel (Open to all)
$server->createChannel('global_chat', [
    'maxClients' => 1000,
    'persistent' => true
]);

// Private Channel (Restricted Access)
$server->createChannel('private_room', [
    'maxClients' => 10,
    'private' => true,
    'auth' => true
]);
```

## 🎈 Event Handling

### Global Events

```php
// Listen to global events
$server->on('connection.open', function($data, $socket) {
    echo "New client connected!";
});

$server->on('message.received', function($data, $socket) {
    // Process incoming messages
});
```

### Channel-Specific Events

```php
// Channel-specific event listener
$server->on('message.received', function($data, $socket, $channel) {
    // Handle messages for a specific channel
}, 'global_chat');
```

## 🔐 Authentication

### User Authentication

```php
// Define authentication method
$server->setAuthenticator(function($credentials) {
    // Validate user credentials
    // Return true if valid, false otherwise
    return $this->validateUser($credentials);
});

// Authenticate client when connecting
$client->authenticate([
    'username' => 'john_doe',
    'token' => 'user_access_token'
]);
```

## 💬 Chat Application Example

### Complete Chat Server Implementation

```php
<?php
use Sentixtech\Websocket\WebsocketServer;

class ChatServer {
    private $server;

    public function __construct() {
        $this->server = new WebsocketServer('0.0.0.0', 8080);
        
        // Create chat channels
        $this->server->createChannel('general', [
            'maxClients' => 500,
            'persistent' => true
        ]);

        $this->server->createChannel('support', [
            'maxClients' => 50,
            'private' => true,
            'auth' => true
        ]);

        // Handle connection events
        $this->server->on('connection.open', function($data, $socket) {
            $this->server->broadcast('A new user joined!', 'general');
        });

        // Handle message events
        $this->server->on('message.received', function($data, $socket) {
            $this->processMessage($data, $socket);
        });
    }

    private function processMessage($data, $socket) {
        // Process and broadcast messages
        $this->server->broadcast($data['message'], $data['channel']);
    }

    public function start() {
        $this->server->start();
    }
}

// Run the chat server
$chatServer = new ChatServer();
$chatServer->start();
```

## 💬 Clustered Chat Application Example

### Server-Side Cluster Implementation

The `ChatClusterManager` demonstrates a complete WebSocket chat cluster with:
- Multiple server nodes
- Load balancing
- Distributed messaging
- Cross-node communication

#### Key Features
- Round-robin node selection
- Redis-based message distribution
- Channel management
- Basic authentication
- Error handling

```php
<?php
use Sentixtech\Websoket\Examples\ChatClusterManager;

// Create a cluster with three nodes
$clusterManager = new ChatClusterManager([
    ['host' => '0.0.0.0', 'port' => 8080],
    ['host' => '0.0.0.0', 'port' => 8081],
    ['host' => '0.0.0.0', 'port' => 8082]
]);

// Start the entire cluster
$clusterManager->startCluster();
```

### Client-Side Implementation

The `ChatClusterClient` provides a robust WebSocket client with:
- Automatic node selection
- Reconnection logic
- Channel subscription
- Message handling

```javascript
// Initialize chat client
const chatClient = new ChatClusterClient([
    { host: 'server1.example.com', port: 8080 },
    { host: 'server2.example.com', port: 8081 },
    { host: 'server3.example.com', port: 8082 }
], 'JohnDoe');

// Connect and interact
chatClient.connect();
chatClient.subscribeToChannel('general');
chatClient.sendMessage('general', 'Hello, clustered chat!');
```

### Cluster Architecture

1. **Load Balancing**: Distribute connections across multiple nodes
2. **Horizontal Scaling**: Add more nodes to increase capacity
3. **Fault Tolerance**: Nodes can be added/removed dynamically
4. **Shared State**: Redis enables cross-node communication

### Performance Considerations

- Use connection pooling
- Implement sticky sessions
- Monitor node health
- Set appropriate connection limits

### Recommended Infrastructure

- Load Balancer (Nginx/HAProxy)
- Redis for message distribution
- Monitoring and logging
- Auto-scaling capabilities

### Deployment Strategies

1. Kubernetes/Docker for containerization
2. Cloud provider managed WebSocket services
3. Custom load balancing solutions
4. Serverless WebSocket platforms

### Limitations and Future Improvements

- Implement more advanced load balancing algorithms
- Add node health checks
- Create a management dashboard
- Enhance authentication mechanisms

### When to Use Clustering

- High-traffic real-time applications
- Global chat systems
- Multiplayer game servers
- Live collaboration tools
- IoT device communication platforms

### Scalability Roadmap

1. Basic single-server implementation
2. Manual clustering (current example)
3. Native clustering support
4. Advanced distributed architecture

## 🔧 Advanced Configuration

### Server Options

```php
$server->setMaxClients(1000)  // Maximum concurrent connections
       ->setPingInterval(30)  // Ping interval in seconds
       ->enableDebug();       // Enable debug mode
```

## 📡 Supported Message Types

- `subscribe`: Join a channel
- `unsubscribe`: Leave a channel
- `publish`: Send a message to a channel
- `event`: Custom event handling

## 🛡️ Security Features

- SSL/TLS Support
- IP Whitelisting
- Rate Limiting
- Message Size Control
- Origin Restrictions

## 📊 Performance Optimization

- Non-blocking I/O
- Connection Pooling
- Efficient Memory Management
- Configurable Client Limits

## 🚧 Requirements

- PHP 7.4+
- Composer
- OpenSSL (for secure connections)

## 📝 License

MIT License

## 🤝 Contributing

Contributions are welcome! Please submit pull requests or open issues on our GitHub repository.

## 📞 Support

- GitHub Issues
- Email: support@sentixtech.com

## 🌟 Use Cases

1. Real-time Chat Applications
2. Live Notifications
3. Collaborative Tools
4. Gaming Servers
5. IoT Device Communication
6. Live Dashboards
7. Stock Market Trackers

## 🔍 Troubleshooting

- Ensure proper firewall configurations
- Check PHP socket extension
- Verify SSL certificates for secure connections
- Monitor server logs for detailed diagnostics

## 🔧 Laravel Service Provider Configuration

### Automatic Package Discovery

Laravel automatically discovers and registers the WebSocket service provider. No manual configuration is required.

### Manual Service Provider Registration

If automatic discovery is disabled, add the service provider to your `config/app.php`:

```php
'providers' => [
    // Other Service Providers
    Sentixtech\Websocket\WebsocketServiceProvider::class,
],
```

### Configuration Publishing

To publish the package configuration, run:

```bash
php artisan vendor:publish --provider="Sentixtech\Websocket\WebsocketServiceProvider" --tag="config"
```

### Environment Configuration

Add WebSocket settings to your `.env` file:

```ini
# WebSocket Server Configuration
WEBSOCKET_HOST=127.0.0.1
WEBSOCKET_PORT=8080
WEBSOCKET_ENABLED=true
WEBSOCKET_MAX_CLIENTS=1000
WEBSOCKET_PING_INTERVAL=30
WEBSOCKET_DEBUG=false

# Authentication and Security
WEBSOCKET_AUTH_ENABLED=false
WEBSOCKET_SSL_CERT_PATH=null
WEBSOCKET_SSL_KEY_PATH=null

# Logging and Monitoring
WEBSOCKET_LOG_CHANNEL=daily
WEBSOCKET_ERROR_LOG=storage/logs/websocket-errors.log
```

These environment variables allow you to customize your WebSocket server's behavior:

- `WEBSOCKET_HOST`: The IP address to bind the WebSocket server
- `WEBSOCKET_PORT`: Port number for the WebSocket server
- `WEBSOCKET_ENABLED`: Enable or disable the WebSocket server
- `WEBSOCKET_MAX_CLIENTS`: Maximum number of concurrent client connections
- `WEBSOCKET_PING_INTERVAL`: Interval (in seconds) for sending ping messages
- `WEBSOCKET_DEBUG`: Enable or disable debug mode
- `WEBSOCKET_AUTH_ENABLED`: Enable or disable authentication
- `WEBSOCKET_SSL_CERT_PATH`: Path to SSL certificate (for secure WebSocket connections)
- `WEBSOCKET_SSL_KEY_PATH`: Path to SSL private key
- `WEBSOCKET_LOG_CHANNEL`: Laravel log channel for WebSocket logs
- `WEBSOCKET_ERROR_LOG`: Path to WebSocket error log file

### Configuration File

After publishing, you'll find the configuration file at `config/websocket.php`. Here's an example configuration:

```php
<?php
return [
    // WebSocket Server Configuration
    'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
    'port' => env('WEBSOCKET_PORT', 8080),
    
    // SSL Configuration
    'ssl' => [
        'enabled' => env('WEBSOCKET_SSL', false),
        'cert_path' => env('WEBSOCKET_SSL_CERT', null),
        'key_path' => env('WEBSOCKET_SSL_KEY', null),
    ],
    
    // Connection Limits
    'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 1000),
    'ping_interval' => env('WEBSOCKET_PING_INTERVAL', 30),
    
    // Debugging
    'debug' => env('WEBSOCKET_DEBUG', false),
];
```

### Environment Variables

You can configure the WebSocket server using `.env` file:

```env
WEBSOCKET_HOST=127.0.0.1
WEBSOCKET_PORT=8080
WEBSOCKET_ENABLED=true
WEBSOCKET_MAX_CLIENTS=1000
WEBSOCKET_PING_INTERVAL=30
WEBSOCKET_DEBUG=false
WEBSOCKET_AUTH_ENABLED=false
WEBSOCKET_SSL_CERT_PATH=null
WEBSOCKET_SSL_KEY_PATH=null
WEBSOCKET_LOG_CHANNEL=daily
WEBSOCKET_ERROR_LOG=storage/logs/websocket-errors.log
```

### Dependency Injection

The WebSocket server can be easily resolved through dependency injection:

```php
use Sentixtech\Websocket\WebsocketServer;

class WebSocketController extends Controller 
{
    public function __construct(WebsocketServer $websocketServer) 
    {
        $this->websocketServer = $websocketServer;
    }
}

## 🌐 Clustering and Load Balancing

### Current Implementation

The current version of the WebSocket server does not have a built-in clustering mechanism. However, there are several strategies you can implement for scaling and load balancing:

### Clustering Strategies

#### 1. Proxy-Based Load Balancing

Use a reverse proxy like Nginx to distribute WebSocket connections:

```nginx
http {
    upstream websocket_cluster {
        # List of WebSocket server nodes
        server server1.example.com:8080;
        server server2.example.com:8080;
        server server3.example.com:8080;
    }

    server {
        listen 80;
        location /ws {
            proxy_pass http://websocket_cluster;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection "upgrade";
        }
    }
}
```

#### 2. Manual Cluster Implementation

Create a basic cluster manager:

```php
<?php
class WebSocketCluster {
    private $nodes = [];
    private $loadBalancingStrategy = 'round_robin';
    private $currentNodeIndex = 0;

    public function __construct(array $nodes, $strategy = 'round_robin') {
        $this->nodes = $nodes;
        $this->loadBalancingStrategy = $strategy;
    }

    public function getNextNode() {
        switch ($this->loadBalancingStrategy) {
            case 'round_robin':
                $node = $this->nodes[$this->currentNodeIndex];
                $this->currentNodeIndex = 
                    ($this->currentNodeIndex + 1) % count($this->nodes);
                return $node;
            
            case 'random':
                return $this->nodes[array_rand($this->nodes)];
            
            default:
                throw new \Exception('Invalid load balancing strategy');
        }
    }
}

// Usage example
$cluster = new WebSocketCluster([
    'ws://server1.example.com',
    'ws://server2.example.com',
    'ws://server3.example.com'
], 'round_robin');

$selectedNode = $cluster->getNextNode();
```

### Recommended Scaling Approaches

1. **Horizontal Scaling**: Deploy multiple WebSocket server instances
2. **Stateless Design**: Minimize server-side state
3. **Shared State**: Use Redis or a distributed cache for shared state
4. **Connection Limits**: Set `max_clients` to prevent overload

### Redis Pub/Sub for Distributed Messaging

```php
<?php
use Redis;

class DistributedWebSocket {
    private $redis;

    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function broadcast($channel, $message) {
        // Publish message across all nodes
        $this->redis->publish($channel, json_encode($message));
    }

    public function subscribe($channel, $callback) {
        $subscriber = new Redis();
        $subscriber->connect('127.0.0.1', 6379);
        $subscriber->subscribe([$channel], $callback);
    }
}
```

### Considerations

- Implement sticky sessions for consistent connections
- Use WebSocket-compatible load balancers
- Monitor connection distribution
- Implement health checks for nodes

### Future Roadmap

We are considering adding native clustering support in future versions. Contributions and feature requests are welcome!
