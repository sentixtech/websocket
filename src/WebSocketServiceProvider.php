<?php

namespace SentixTech\WebSocket;

use Illuminate\Support\ServiceProvider;
use SentixTech\WebSocket\Console\WebSocketServeCommand;
use SentixTech\WebSocket\Services\WebSocketService;

class WebSocketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/websocket.php', 'websocket');
        
        $this->app->singleton('websocket', function ($app) {
            return new WebSocketService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebSocketServeCommand::class
            ]);

            $this->publishes([
                __DIR__.'/Config/websocket.php' => config_path('websocket.php')
            ], 'websocket-config');
        }
    }
}
