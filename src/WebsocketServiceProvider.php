<?php

namespace Sentixtech\Websocket;

use Illuminate\Support\ServiceProvider;

class WebsocketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WebsocketServer::class, function ($app) {
            return new WebsocketServer();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/websocket.php' => config_path('websocket.php'),
            ], 'config');
        }
    }
}
