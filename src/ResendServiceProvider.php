<?php

namespace Resend\Laravel;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Resend;
use Resend\Client;
use Resend\Laravel\Transport\ResendTransportFactory;

final class ResendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
        $this->app->singleton(Client::class, static function (): Client {
            $apiKey = config('resend.api_key');

            if (! is_string($apiKey)) {
                throw new InvalidArgumentException('API key missing');
            }

            return Resend::client($apiKey);
        });

        $this->app->alias(Client::class, 'resend');
    }

    /**
     * Register the package's publishable assets.
     */
    protected function registerPublishing(): void
    {
        if (method_exists($this->app, 'runningInConsole') && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/resend.php' => $this->app->configPath('resend.php'),
            ], 'resend-config');
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
