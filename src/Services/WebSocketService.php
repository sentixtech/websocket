<?php

namespace SentixTech\WebSocket\Services;

use Exception;
use SplObjectStorage;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    // WebSocket GUID for handshake
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    // WebSocket frame opcodes
    const OPCODE_CONTINUATION = 0x0;
    const OPCODE_TEXT = 0x1;
    const OPCODE_BINARY = 0x2;
    const OPCODE_CLOSE = 0x8;
    const OPCODE_PING = 0x9;
    const OPCODE_PONG = 0xA;

    // WebSocket configuration
    protected $config;

    // Socket management
    protected $server;
    protected $clients;
    
    // Advanced channel and user management
    protected $channels = [];
    protected $userChannels = [];
    protected $channelUsers = [];

    public function __construct(array $config = [])
    {
        $this->clients = new SplObjectStorage();
        $this->config = array_merge([
            'host' => '0.0.0.0',  // Listen on all interfaces
            'port' => 8080,
            'max_clients' => 1000,
            'max_frame_size' => 1024 * 1024, // 1MB
            'timeout' => 30,
            'allowed_origins' => ['*'], // Allow all origins by default
            'ping_interval' => 30, // Ping every 30 seconds
            'reconnect_timeout' => 10, // Reconnect after 10 seconds
            'log_level' => 'debug' // Logging verbosity
        ], $config);
    }

    /**
     * Create a dynamic channel
     * 
     * @param string $channelName
     * @return bool
     */
    public function createChannel(string $channelName)
    {
        if (!isset($this->channels[$channelName])) {
            $this->channels[$channelName] = new SplObjectStorage();
            $this->channelUsers[$channelName] = [];
            return true;
        }
        return false;
    }

    /**
     * Subscribe a client to a channel
     * 
     * @param resource $client
     * @param string $channelName
     * @param int|null $userId Optional user ID for tracking
     * @return bool
     */
    public function subscribeToChannel($client, string $channelName, ?int $userId = null)
    {
        // Ensure channel exists
        if (!isset($this->channels[$channelName])) {
            $this->createChannel($channelName);
        }

        // Add client to channel
        if (!$this->channels[$channelName]->contains($client)) {
            $this->channels[$channelName]->attach($client);
        }

        // Track user if provided
        if ($userId !== null) {
            $this->userChannels[$userId][$channelName] = true;
            $this->channelUsers[$channelName][$userId] = $client;
        }

        return true;
    }

    /**
     * Unsubscribe a client from a channel
     * 
     * @param resource $client
     * @param string $channelName
     * @param int|null $userId Optional user ID
     * @return bool
     */
    public function unsubscribeFromChannel($client, string $channelName, ?int $userId = null)
    {
        if (isset($this->channels[$channelName])) {
            $this->channels[$channelName]->detach($client);

            // Remove user tracking
            if ($userId !== null) {
                unset($this->userChannels[$userId][$channelName]);
                unset($this->channelUsers[$channelName][$userId]);
            }
        }

        return true;
    }

    /**
     * Broadcast a message to a specific channel
     * 
     * @param string $channelName
     * @param mixed $message
     * @param array $options Additional broadcast options
     * @return int Number of successful broadcasts
     */
    public function broadcast(string $channelName, $message, array $options = [])
    {
        // Normalize message to string
        $payload = is_string($message) ? $message : json_encode($message);

        // Default options
        $excludeClients = $options['exclude'] ?? [];
        $specificUsers = $options['users'] ?? null;

        // Validate channel
        if (!isset($this->channels[$channelName])) {
            Log::warning("Broadcast to non-existent channel: {$channelName}");
            return 0;
        }

        $successCount = 0;

        // Broadcast logic
        foreach ($this->channels[$channelName] as $client) {
            // Skip excluded clients
            if (in_array($client, $excludeClients, true)) {
                continue;
            }

            // User-specific broadcast
            if ($specificUsers !== null) {
                $matchedUser = false;
                foreach ($specificUsers as $userId) {
                    if (isset($this->channelUsers[$channelName][$userId]) && 
                        $this->channelUsers[$channelName][$userId] === $client) {
                        $matchedUser = true;
                        break;
                    }
                }
                if (!$matchedUser) {
                    continue;
                }
            }

            try {
                // Encode and send message
                $encodedMessage = $this->encodeFrame($payload);
                $result = @socket_write($client, $encodedMessage);
                
                if ($result !== false) {
                    $successCount++;
                }
            } catch (Exception $e) {
                Log::error("Broadcast error in channel {$channelName}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $successCount;
    }

    /**
     * Send a generic event with flexible payload
     * 
     * @param string $eventName
     * @param mixed $data
     * @param array $options
     * @return int
     */
    public function emit(string $eventName, $data, array $options = [])
    {
        // Prepare event payload
        $payload = json_encode([
            'event' => $eventName,
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);

        // Use broadcast method with event name as channel
        return $this->broadcast($eventName, $payload, $options);
    }

    /**
     * Get channel subscribers
     * 
     * @param string $channelName
     * @return int
     */
    public function getChannelSubscribers(string $channelName): int
    {
        return isset($this->channels[$channelName]) 
            ? $this->channels[$channelName]->count() 
            : 0;
    }

    public function createServer()
    {
        // Create a non-blocking socket
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        // Set socket options
        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_nonblock($this->server);
        
        // Bind the socket
        socket_bind($this->server, $this->config['host'], $this->config['port']);
        
        // Start listening
        socket_listen($this->server, $this->config['max_clients']);
        
        return $this;
    }

    public function receiveMessages()
    {
        // Ensure clients exist and convert to array
        $read = $this->clients->count() > 0 ? iterator_to_array($this->clients) : [];
        
        // If no clients, return early
        if (empty($read)) {
            usleep(100000); // 100ms sleep to prevent tight loop
            return;
        }

        $write = [];
        $except = [];

        try {
            // Use a timeout to prevent blocking
            $ready = @socket_select($read, $write, $except, 0, 10000);
            
            if ($ready === false || $ready == 0) {
                usleep(100000); // 100ms sleep to prevent tight loop
                return;
            }

            foreach ($read as $client) {
                try {
                    $this->processClientMessage($client);
                } catch (\Exception $e) {
                    $this->disconnectClient($client);
                }
            }
        } catch (\Exception $e) {
            usleep(100000); // 100ms sleep to prevent tight loop
        }
    }

    protected function processClientMessage($client)
    {
        // Read the frame
        $frame = socket_read($client, $this->config['max_frame_size']);
        
        if ($frame === false || $frame === '') {
            // Client disconnected
            $this->disconnectClient($client);
            return;
        }

        // Decode WebSocket frame
        $decoded = $this->decodeFrame($frame);
        
        switch ($decoded['opcode']) {
            case self::OPCODE_TEXT:
                $this->handleTextMessage($client, $decoded['payload']);
                break;
            case self::OPCODE_CLOSE:
                $this->disconnectClient($client);
                break;
            case self::OPCODE_PING:
                $this->sendPong($client);
                break;
        }
    }

    protected function decodeFrame($frame)
    {
        try {
            // Ensure frame has at least 2 bytes
            if (strlen($frame) < 2) {
                return null;
            }

            $firstByte = ord($frame[0]);
            $secondByte = ord($frame[1]);
            
            $opcode = $firstByte & 0x0F;
            $isMasked = ($secondByte & 0x80) !== 0;
            $payloadLength = $secondByte & 0x7F;

            $offset = 2;
            if ($payloadLength === 126) {
                // Next 2 bytes are the length
                if (strlen($frame) < $offset + 2) {
                    return null;
                }
                $payloadLength = unpack('n', substr($frame, $offset, 2))[1];
                $offset += 2;
            } elseif ($payloadLength === 127) {
                // Next 8 bytes are the length
                if (strlen($frame) < $offset + 8) {
                    return null;
                }
                $payloadLength = unpack('J', substr($frame, $offset, 8))[1];
                $offset += 8;
            }

            // Handle masked payload
            $maskKey = null;
            if ($isMasked) {
                if (strlen($frame) < $offset + 4) {
                    return null;
                }
                $maskKey = substr($frame, $offset, 4);
                $offset += 4;
            }

            // Extract payload
            if (strlen($frame) < $offset + $payloadLength) {
                return null;
            }

            $payload = substr($frame, $offset, $payloadLength);

            // Unmask payload if needed
            if ($isMasked && $maskKey) {
                $unmaskedPayload = '';
                for ($i = 0; $i < $payloadLength; $i++) {
                    $unmaskedPayload .= $payload[$i] ^ $maskKey[$i % 4];
                }
                $payload = $unmaskedPayload;
            }

            return [
                'opcode' => $opcode,
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function handleTextMessage($client, $message)
    {
        try {
            // More robust JSON parsing with detailed error handling
            $data = json_decode($message, true);
            
            // Validate JSON parsing
            $jsonError = json_last_error();
            if ($jsonError !== JSON_ERROR_NONE) {
                return;
            }

            // Validate message structure with detailed logging
            if (!is_array($data)) {
                return;
            }

            // Check for type with safe access
            $type = $data['type'] ?? null;
            
            if ($type === null) {
                return;
            }

            // Process message types with comprehensive error handling
            switch ($type) {
                case 'subscribe':
                    $channel = $data['channel'] ?? null;
                    if (!$channel) {
                        return;
                    }
                    $this->subscribeToChannel($client, $channel);
                    break;

                case 'unsubscribe':
                    $channel = $data['channel'] ?? null;
                    if (!$channel) {
                        return;
                    }
                    $this->unsubscribeFromChannel($client, $channel);
                    break;

                default:
                    return;
            }
        } catch (\Exception $e) {
            $this->disconnectClient($client);
        }
    }

    protected function encodeFrame($payload)
    {
        try {
            // Determine frame length encoding
            if (strlen($payload) <= 125) {
                $header = chr(0x81) . chr(strlen($payload));
            } elseif (strlen($payload) <= 65535) {
                $header = chr(0x81) . chr(126) . pack('n', strlen($payload));
            } else {
                $header = chr(0x81) . chr(127) . pack('J', strlen($payload));
            }

            return $header . $payload;
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function sendPong($client)
    {
        $pongFrame = chr(0x8A) . chr(0); // Pong frame
        socket_write($client, $pongFrame);
    }

    protected function disconnectClient($client)
    {
        // Remove from all channels
        foreach ($this->channels as $channel) {
            if ($channel->contains($client)) {
                $channel->detach($client);
            }
        }

        // Remove from clients
        $this->clients->detach($client);
        
        // Close socket
        socket_close($client);
    }

    public function serve($host = null, $port = null)
    {
        // Merge provided host and port with existing configuration
        $host = $host ?? $this->config['host'] ?? '0.0.0.0';
        $port = $port ?? $this->config['port'] ?? 8080;

        // Create server socket
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->server === false) {
            throw new \Exception("Failed to create socket: " . socket_strerror(socket_last_error()));
        }

        // Set socket options
        socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_nonblock($this->server);

        // Bind socket
        if (!socket_bind($this->server, $host, $port)) {
            throw new \Exception("Failed to bind socket: " . socket_strerror(socket_last_error($this->server)));
        }

        // Start listening
        if (!socket_listen($this->server, $this->config['max_clients'])) {
            throw new \Exception("Failed to listen on socket: " . socket_strerror(socket_last_error($this->server)));
        }

        echo "WebSocket server started on {$host}:{$port}\n";

        // Main server loop
        while (true) {
            $read = [$this->server];
            $write = $except = [];

            // Check for new connections
            $ready = @socket_select($read, $write, $except, null);

            if ($ready === false) {
                echo "Socket select error: " . socket_strerror(socket_last_error()) . "\n";
                break;
            }

            if ($ready > 0) {
                // Accept new client
                $client = @socket_accept($this->server);
                
                if ($client !== false) {
                    socket_set_nonblock($client);
                    
                    // Perform handshake
                    if ($this->performHandshake($client)) {
                        $this->clients->attach($client);
                        echo "New client connected\n";
                    } else {
                        socket_close($client);
                    }
                }
            }

            // Process messages from existing clients
            $this->receiveMessages();
        }

        // Close server socket
        socket_close($this->server);
    }

    protected function handleConnections()
    {
        $read = [$this->server];
        $write = $except = [];

        // Check for new connections with timeout
        $ready = @socket_select($read, $write, $except, 0, 10000);
        
        if ($ready > 0) {
            $client = @socket_accept($this->server);
            
            if ($client !== false) {
                @socket_set_nonblock($client);
                
                // Perform WebSocket handshake
                $handshakeResult = $this->performHandshake($client);

                if ($handshakeResult) {
                    // Add client to tracked clients
                    $this->clients->attach($client);
                } else {
                    // Failed handshake, close connection
                    @socket_close($client);
                }
            }
        }
    }

    protected function performHandshake($socket)
    {
        try {
            // Increase read buffer size and use PHP_BINARY_READ
            $request = @socket_read($socket, 4096, PHP_BINARY_READ);
            
            if ($request === false) {
                return false;
            }

            // Parse request headers
            $headers = $this->parseHeaders($request);

            // Validate WebSocket key with more comprehensive checks
            if (!isset($headers['Sec-Websocket-Key'])) {
                return false;
            }

            // Validate WebSocket version
            $version = $headers['Sec-Websocket-Version'] ?? null;
            if ($version !== '13') {
                return false;
            }

            // Origin validation
            $origin = $headers['Origin'] ?? null;
            $allowedOrigins = $this->config['allowed_origins'] ?? ['*'];
            
            if ($origin && $allowedOrigins !== ['*'] && !in_array($origin, $allowedOrigins)) {
                return false;
            }

            $key = $headers['Sec-Websocket-Key'];

            // Generate accept key with robust error handling
            $acceptKey = base64_encode(sha1($key . self::GUID, true));

            // Prepare handshake response
            $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: {$acceptKey}\r\n" .
                ($origin ? "Access-Control-Allow-Origin: {$origin}\r\n" : "") .
                "Sec-WebSocket-Version: 13\r\n\r\n";

            // Send handshake response
            $result = @socket_write($socket, $response);

            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function parseHeaders($request)
    {
        $headers = [];
        $lines = explode("\r\n", trim($request));

        // First line is the request line, skip it
        array_shift($lines);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                break; // Headers end with an empty line
            }

            $parts = explode(':', $line, 2);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Normalize header keys
                $normalizedKey = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $key))));
                $headers[$normalizedKey] = $value;
            }
        }

        return $headers;
    }
}
