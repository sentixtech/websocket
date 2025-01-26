<?php

namespace Sentixtech\Websocket;

class WebsocketServer {
    protected $server;
    protected $clients = [];
    protected $channels = [];
    protected $events = [];
    protected $customFunctions = [];
    protected $defaultEvents = [
        'connection.open',
        'connection.close',
        'channel.subscribe',
        'channel.unsubscribe',
        'message.received',
        'message.sent',
        'error'
    ];

    protected $debug = false;
    protected $maxClients = 1000;
    protected $pingInterval = 30;
    protected $lastPing = [];
    protected $host;
    protected $port;
    protected $isSecure = false;
    protected $certFile;
    protected $keyFile;
    protected $bufferSize = 8192;

    protected function detectHost() {
        if (PHP_SAPI === 'cli') {
            return '0.0.0.0';
        }
        return $_SERVER['SERVER_NAME'] ?? '127.0.0.1';
    }

    protected function detectPort() {
        $port = 8080;
        while (!$this->isPortAvailable($port) && $port < 9000) {
            $port++;
        }
        return $port;
    }

    protected function isPortAvailable($port) {
        $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($sock === false) return false;
        $result = @socket_bind($sock, $this->host, $port);
        socket_close($sock);
        return $result !== false;
    }

    protected function registerDefaultEvents() {
        foreach ($this->defaultEvents as $event) {
            $this->events[$event] = [];
        }
    }

    protected function detectSSL() {
        // Common SSL certificate locations
        $sslLocations = [
            // Apache locations
            '/etc/ssl/certs/apache-selfsigned.crt' => '/etc/ssl/private/apache-selfsigned.key',
            '/etc/apache2/ssl/apache.crt' => '/etc/apache2/ssl/apache.key',
            // XAMPP locations
            'D:/xampp/apache/conf/ssl.crt/server.crt' => 'D:/xampp/apache/conf/ssl.key/server.key',
            'C:/xampp/apache/conf/ssl.crt/server.crt' => 'C:/xampp/apache/conf/ssl.key/server.key',
            // cPanel locations
            '/var/cpanel/ssl/apache_tls/domain.com/combined' => '/var/cpanel/ssl/apache_tls/domain.com/private',
            // Let's Encrypt locations
            '/etc/letsencrypt/live/domain.com/fullchain.pem' => '/etc/letsencrypt/live/domain.com/privkey.pem',
            // Default SSL locations
            '/etc/ssl/certs/ssl-cert-snakeoil.pem' => '/etc/ssl/private/ssl-cert-snakeoil.key',
        ];

        // Try to detect server software
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $isXampp = stripos($serverSoftware, 'xampp') !== false;
        $isApache = stripos($serverSoftware, 'apache') !== false;
        $isCpanel = file_exists('/usr/local/cpanel/version');

        // Get server name
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // Replace domain.com with actual domain in paths
        $sslLocations = array_combine(
            array_map(function($path) use ($serverName) {
                return str_replace('domain.com', $serverName, $path);
            }, array_keys($sslLocations)),
            array_map(function($path) use ($serverName) {
                return str_replace('domain.com', $serverName, $path);
            }, array_values($sslLocations))
        );

        // Check for existing SSL certificates
        foreach ($sslLocations as $cert => $key) {
            if (file_exists($cert) && file_exists($key)) {
                return [
                    'cert_file' => $cert,
                    'key_file' => $key
                ];
            }
        }

        // Try to detect from Apache configuration
        if ($isApache || $isXampp) {
            $possibleConfigs = [
                'D:/xampp/apache/conf/httpd.conf',
                'C:/xampp/apache/conf/httpd.conf',
                '/etc/apache2/apache2.conf',
                '/etc/httpd/conf/httpd.conf'
            ];

            foreach ($possibleConfigs as $config) {
                if (file_exists($config)) {
                    $content = file_get_contents($config);
                    if (preg_match('/SSLCertificateFile\s+"?([^"\n]+)"?/i', $content, $certMatch) &&
                        preg_match('/SSLCertificateKeyFile\s+"?([^"\n]+)"?/i', $content, $keyMatch)) {
                        if (file_exists($certMatch[1]) && file_exists($keyMatch[1])) {
                            return [
                                'cert_file' => $certMatch[1],
                                'key_file' => $keyMatch[1]
                            ];
                        }
                    }
                }
            }
        }

        // Check for cPanel SSL
        if ($isCpanel) {
            $sslDir = "/var/cpanel/ssl/installed/";
            if (is_dir($sslDir)) {
                foreach (scandir($sslDir) as $domain) {
                    if ($domain !== '.' && $domain !== '..') {
                        $certFile = $sslDir . $domain . "/combined";
                        $keyFile = $sslDir . $domain . "/private";
                        if (file_exists($certFile) && file_exists($keyFile)) {
                            return [
                                'cert_file' => $certFile,
                                'key_file' => $keyFile
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    public function __construct($host = null, $port = null) {
        $this->host = $host ?: $this->detectHost();
        $this->port = $port ?: $this->detectPort();
        
        // Auto-detect SSL
        $sslConfig = $this->detectSSL();
        if ($sslConfig) {
            $this->isSecure = true;
            $this->certFile = $sslConfig['cert_file'];
            $this->keyFile = $sslConfig['key_file'];
            $this->log("SSL detected: {$this->certFile}");
        }

        $this->registerDefaultEvents();
    }

    public function createChannel($name, $options = []) {
        if (!isset($this->channels[$name])) {
            $this->channels[$name] = [
                'name' => $name,
                'clients' => [],
                'options' => array_merge([
                    'maxClients' => 0, // 0 means unlimited
                    'private' => false,
                    'persistent' => false,
                    'events' => []
                ], $options)
            ];
            $this->log("Channel created: $name");
        }
        return $this;
    }

    public function defineFunction($name, callable $callback) {
        $this->customFunctions[$name] = $callback;
        return $this;
    }

    public function on($event, callable $callback, $channel = null) {
        if ($channel) {
            if (!isset($this->channels[$channel])) {
                $this->createChannel($channel);
            }
            $this->channels[$channel]['options']['events'][$event][] = $callback;
        } else {
            $this->events[$event][] = $callback;
        }
        return $this;
    }

    public function enableDebug() {
        $this->debug = true;
        return $this;
    }

    public function setMaxClients($max) {
        $this->maxClients = $max;
        return $this;
    }

    public function setPingInterval($seconds) {
        $this->pingInterval = $seconds;
        return $this;
    }

    public function enableSSL($certFile, $keyFile) {
        $this->isSecure = true;
        $this->certFile = $certFile;
        $this->keyFile = $keyFile;
        return $this;
    }

    public function start() {
        $context = stream_context_create();
        
        if ($this->isSecure) {
            stream_context_set_option($context, 'ssl', 'local_cert', $this->certFile);
            stream_context_set_option($context, 'ssl', 'local_pk', $this->keyFile);
            stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
            $protocol = 'ssl';
        } else {
            $protocol = 'tcp';
        }

        $this->server = stream_socket_server(
            "$protocol://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );

        if (!$this->server) {
            throw new \Exception("Could not start server: $errstr ($errno)");
        }

        $this->log("WebSocket server started on {$this->host}:{$this->port}");
        
        while (true) {
            $this->checkTimeouts();
            
            $read = $this->clients;
            $read[] = $this->server;
            $write = $except = null;
            
            if (@stream_select($read, $write, $except, 0, 200000) > 0) {
                foreach ($read as $socket) {
                    if ($socket === $this->server) {
                        $this->acceptNewClient();
                    } else {
                        $this->handleClientData($socket);
                    }
                }
            }
        }
    }

    protected function acceptNewClient() {
        if (count($this->clients) >= $this->maxClients) {
            $client = @stream_socket_accept($this->server);
            if ($client) {
                fclose($client);
            }
            return;
        }

        $client = @stream_socket_accept($this->server);
        if ($client) {
            stream_set_blocking($client, false);
            $this->clients[intval($client)] = [
                'socket' => $client,
                'handshake' => false,
                'channels' => [],
                'buffer' => ''
            ];
            $this->lastPing[intval($client)] = time();
            $this->log("New client connected");
        }
    }

    protected function handleClientData($socket) {
        $data = fread($socket, $this->bufferSize);
        $clientId = intval($socket);

        if (!isset($this->clients[$clientId])) {
            return;
        }

        if ($data === false || strlen($data) === 0) {
            $this->disconnect($socket);
            return;
        }

        $client = &$this->clients[$clientId];
        
        if (!$client['handshake']) {
            $this->doHandshake($socket, $data);
        } else {
            $messages = $this->decodeWebSocketFrames($data);
            foreach ($messages as $message) {
                $this->handleMessage($socket, $message);
            }
        }
    }

    protected function doHandshake($socket, $headers) {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/i", $headers, $match)) {
            $acceptKey = base64_encode(pack('H*', sha1($match[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                      "Upgrade: websocket\r\n" .
                      "Connection: Upgrade\r\n" .
                      "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";

            fwrite($socket, $upgrade);
            $this->clients[intval($socket)]['handshake'] = true;
            $this->log("Handshake completed");
        }
    }

    protected function handleMessage($socket, $message) {
        $data = json_decode($message, true);
        if (!$data || !isset($data['type'])) return;

        $clientId = intval($socket);
        
        // Trigger message.received event
        $this->triggerEvent('message.received', $data, $socket);

        switch ($data['type']) {
            case 'subscribe':
                $this->handleSubscribe($socket, $data);
                break;
            case 'unsubscribe':
                $this->handleUnsubscribe($socket, $data);
                break;
            case 'publish':
                $this->handlePublish($socket, $data);
                break;
            case 'call':
                $this->handleFunctionCall($socket, $data);
                break;
            case 'event':
                $this->handleCustomEvent($socket, $data);
                break;
            case 'createChannel':
                $this->handleCreateChannel($socket, $data);
                break;
        }
    }

    protected function handleCreateChannel($socket, $data) {
        if (!isset($data['channel'])) return;
        
        $name = $data['channel'];
        $options = $data['options'] ?? [];
        
        $this->createChannel($name, $options);
        $this->sendToClient($socket, [
            'type' => 'channel.created',
            'channel' => $name
        ]);
    }

    protected function handleSubscribe($socket, $data) {
        if (!isset($data['channel'])) return;
        
        $clientId = intval($socket);
        $channel = $data['channel'];
        
        if (!isset($this->channels[$channel])) {
            $this->createChannel($channel);
        }
        
        // Check channel limits
        if ($this->channels[$channel]['options']['maxClients'] > 0 && 
            count($this->channels[$channel]['clients']) >= $this->channels[$channel]['options']['maxClients']) {
            $this->sendError($socket, "Channel $channel is full");
            return;
        }
        
        $this->channels[$channel]['clients'][$clientId] = $this->clients[$clientId];
        $this->clients[$clientId]['channels'][] = $channel;
        
        $this->triggerEvent('channel.subscribe', [
            'channel' => $channel,
            'client' => $clientId
        ], $socket);
        
        $this->sendToClient($socket, [
            'type' => 'subscribed',
            'channel' => $channel
        ]);
    }

    protected function handleFunctionCall($socket, $data) {
        if (!isset($data['function']) || !isset($this->customFunctions[$data['function']])) {
            $this->sendError($socket, 'Function not found');
            return;
        }
        
        try {
            $result = call_user_func($this->customFunctions[$data['function']], 
                $data['params'] ?? [], 
                $socket
            );
            
            $this->sendToClient($socket, [
                'type' => 'function.result',
                'function' => $data['function'],
                'result' => $result
            ]);
        } catch (\Exception $e) {
            $this->sendError($socket, $e->getMessage());
        }
    }

    protected function triggerEvent($event, $data, $socket = null) {
        // Global events
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                call_user_func($callback, $data, $socket);
            }
        }
        
        // Channel specific events
        if ($socket && isset($this->clients[intval($socket)])) {
            $client = $this->clients[intval($socket)];
            foreach ($client['channels'] as $channel) {
                if (isset($this->channels[$channel]['options']['events'][$event])) {
                    foreach ($this->channels[$channel]['options']['events'][$event] as $callback) {
                        call_user_func($callback, $data, $socket, $channel);
                    }
                }
            }
        }
    }

    protected function sendError($socket, $message) {
        $this->sendToClient($socket, [
            'type' => 'error',
            'message' => $message
        ]);
        $this->triggerEvent('error', ['message' => $message], $socket);
    }

    protected function sendToClient($socket, $data) {
        $encoded = $this->encodeWebSocketFrame(json_encode($data));
        @fwrite($socket, $encoded);
    }

    protected function broadcast($data, $channel = null, $exclude = null) {
        $message = [
            'type' => 'message',
            'channel' => $channel,
            'data' => $data,
            'timestamp' => time()
        ];

        $encoded = $this->encodeWebSocketFrame(json_encode($message));
        
        if ($channel && isset($this->channels[$channel])) {
            foreach ($this->channels[$channel]['clients'] as $clientId => $client) {
                if ($exclude && intval($exclude) === $clientId) continue;
                @fwrite($client['socket'], $encoded);
            }
        } else {
            foreach ($this->clients as $clientId => $client) {
                if ($exclude && intval($exclude) === $clientId) continue;
                @fwrite($client['socket'], $encoded);
            }
        }
    }

    protected function checkTimeouts() {
        $time = time();
        foreach ($this->lastPing as $clientId => $lastPing) {
            if ($time - $lastPing > $this->pingInterval * 2) {
                if (isset($this->clients[$clientId])) {
                    $this->disconnect($this->clients[$clientId]['socket']);
                }
            }
        }
    }

    protected function disconnect($socket) {
        $clientId = intval($socket);
        
        // Remove from channels
        foreach ($this->channels as &$channel) {
            unset($channel['clients'][$clientId]);
        }
        
        // Clean up client data
        unset($this->clients[$clientId]);
        unset($this->lastPing[$clientId]);
        
        @fclose($socket);
        $this->log("Client disconnected");
    }

    protected function decodeWebSocketFrames($data) {
        $messages = [];
        $buffer = $data;
        
        while (strlen($buffer) > 0) {
            $payload = $this->decodeWebSocketFrame($buffer);
            if ($payload === false) break;
            
            if ($payload !== null) {
                $messages[] = $payload;
            }
        }
        
        return $messages;
    }

    protected function decodeWebSocketFrame(&$buffer) {
        if (strlen($buffer) < 2) return false;
        
        $firstByte = ord($buffer[0]);
        $secondByte = ord($buffer[1]);
        
        $fin = ($firstByte & 0x80) != 0;
        $opcode = $firstByte & 0x0F;
        $masked = ($secondByte & 0x80) != 0;
        $payloadLen = $secondByte & 0x7F;
        
        $offset = 2;
        
        if ($payloadLen === 126) {
            if (strlen($buffer) < 4) return false;
            $payloadLen = unpack('n', substr($buffer, 2, 2))[1];
            $offset += 2;
        } elseif ($payloadLen === 127) {
            if (strlen($buffer) < 10) return false;
            $payloadLen = unpack('J', substr($buffer, 2, 8))[1];
            $offset += 8;
        }
        
        if ($masked) {
            if (strlen($buffer) < $offset + 4) return false;
            $masks = substr($buffer, $offset, 4);
            $offset += 4;
        }
        
        if (strlen($buffer) < $offset + $payloadLen) return false;
        
        $payload = substr($buffer, $offset, $payloadLen);
        $buffer = substr($buffer, $offset + $payloadLen);
        
        if ($masked) {
            for ($i = 0; $i < strlen($payload); $i++) {
                $payload[$i] = chr(ord($payload[$i]) ^ ord($masks[$i % 4]));
            }
        }
        
        switch ($opcode) {
            case 0x1: // Text frame
                return $payload;
            case 0x8: // Close frame
                return null;
            case 0x9: // Ping frame
                $this->sendPong($payload);
                return null;
            case 0xA: // Pong frame
                return null;
            default:
                return $payload;
        }
    }

    protected function encodeWebSocketFrame($payload, $opcode = 0x1) {
        $firstByte = 0x80 | $opcode; // FIN + opcode
        $len = strlen($payload);
        
        if ($len <= 125) {
            $header = pack('CC', $firstByte, $len);
        } elseif ($len <= 65535) {
            $header = pack('CCn', $firstByte, 126, $len);
        } else {
            $header = pack('CCJ', $firstByte, 127, $len);
        }
        
        return $header . $payload;
    }

    protected function sendPong($payload) {
        $frame = $this->encodeWebSocketFrame($payload, 0xA);
        fwrite($this->server, $frame);
    }

    protected function log($message) {
        if ($this->debug) {
            $time = date('Y-m-d H:i:s');
            echo "[$time] $message\n";
        }
    }
}
