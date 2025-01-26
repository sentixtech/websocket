<?php

namespace Sentixtech\Websocket\Console;

use Illuminate\Console\Command;
use Sentixtech\Websocket\WebsocketServer;

class WebsocketServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve 
                            {--host=0.0.0.0 : The host to bind the WebSocket server to}
                            {--port=8080 : The port to run the WebSocket server on}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the WebSocket server';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info("Starting WebSocket server on {$host}:{$port}");

        $server = new WebsocketServer($host, $port);
        $server->start();
    }
}
