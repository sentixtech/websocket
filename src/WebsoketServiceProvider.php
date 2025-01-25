<?php

namespace Sentixtech\Websoket;

use Illuminate\Support\ServiceProvider;

class WebsoketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WebsoketServer::class, function ($app) {
            return new WebsoketServer();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/websoket.php' => config_path('websoket.php'),
            ], 'config');
        }
    }
}
