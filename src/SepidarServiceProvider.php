<?php

namespace Ahmadi\LaravelSepidar;

use Ahmadi\LaravelSepidar\Client\SepidarClient;
use Ahmadi\LaravelSepidar\Console\SetupCommand;
use Ahmadi\LaravelSepidar\Contracts\SepidarClientInterface;
use Illuminate\Support\ServiceProvider;

class SepidarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sepidar.php',
            'sepidar'
        );

        $this->app->singleton(SepidarClient::class, function ($app) {
            return new SepidarClient($app['config']['sepidar']);
        });

        $this->app->singleton(SepidarClientInterface::class, SepidarClient::class);

        $this->app->singleton(SepidarManager::class, function ($app) {
            return new SepidarManager($app->make(SepidarClient::class));
        });

        $this->app->alias(SepidarClientInterface::class, 'sepidar.client');
        $this->app->alias(SepidarManager::class, 'sepidar');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sepidar.php' => config_path('sepidar.php'),
            ], 'sepidar-config');

            $this->commands([
                SetupCommand::class,
            ]);
        }
    }
}
