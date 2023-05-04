<?php

namespace Resend\Laravel;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Resend;
use Resend\Client;
use Resend\Contracts\Client as ClientContract;
use Resend\Laravel\Exceptions\ApiKeyIsMissing;
use Resend\Laravel\Transport\ResendTransportFactory;

class ResendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerPublishing();

        Mail::extend('resend', function (array $config = []) {
            return new ResendTransportFactory($this->app['resend'], $config['options'] ?? []);
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configure();
        $this->bindResendClient();
    }

    /**
     * Setup the configuration for Resend.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/resend.php', 'resend'
        );
    }

    /**
     * Bind the Resend Client.
     */
    protected function bindResendClient(): void
    {
        $this->app->singleton(ClientContract::class, static function (): Client {
            $apiKey = config('resend.api_key');

            if (! is_string($apiKey)) {
                throw ApiKeyIsMissing::create();
            }

            return Resend::client($apiKey);
        });

        $this->app->alias(ClientContract::class, 'resend');
        $this->app->alias(ClientContract::class, Client::class);
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'domain' => config('resend.domain', null),
            'namespace' => 'Resend\Laravel\Http\Controllers',
            'prefix' => config('resend.path'),
            'as' => 'resend.',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Register the package's publishable assets.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/resend.php' => $this->app->configPath('resend.php'),
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            Client::class,
        ];
    }
}
