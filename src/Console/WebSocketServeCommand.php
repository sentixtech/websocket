<?php

namespace SentixTech\WebSocket\Console;

use Illuminate\Console\Command;
use SentixTech\WebSocket\Services\WebSocketService;

class WebSocketServeCommand extends Command
{
    protected $signature = 'websocket:serve 
                            {--host=localhost : The host to bind the WebSocket server to}
                            {--port=8080 : The port to run the WebSocket server on}';

    protected $description = 'Start a WebSocket server';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("Starting WebSocket server on {$host}:{$port}");

        $websocket = new WebSocketService();
        $websocket->serve($host, $port);
    }
}
