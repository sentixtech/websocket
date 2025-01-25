# Sentixtech WebSocket Server for PHP

A powerful, easy-to-use WebSocket server implementation for PHP applications.

## Overview

This package provides a lightweight, dependency-free WebSocket server for PHP applications, supporting various server environments including Apache, XAMPP, and cPanel.

## Features

- 🚀 Zero Dependencies
- 🔒 Secure SSL/TLS Support
- 🎯 Custom Channel Management
- ⚡ High-Performance WebSocket Server
- 📝 Framework Agnostic

## Requirements

- PHP 7.4+
- Composer

## Installation

Install the package via Composer:

```bash
composer require sentixtech/websocket
```

## Composer Configuration

Add the following to your `composer.json`:

```json
{
    "name": "your-vendor/your-project",
    "type": "project",
    "require": {
        "sentixtech/websocket": "^1.0"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./packages/websocket"
        }
    ]
}
```

## Quick Start

### Server Setup

```php
<?php
use Sentixtech\Websocket\WebsocketServer;

$server = new WebsocketServer();
$server->createChannel('chat');
$server->start();
```

## Documentation

For detailed documentation, configuration options, and advanced usage, please refer to our [documentation](https://github.com/sentixtech/websocket/docs).

## Contributing

Contributions are welcome! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Support

- GitHub Issues: [https://github.com/sentixtech/websocket/issues](https://github.com/sentixtech/websocket/issues)
- Email: support@sentixtech.com
