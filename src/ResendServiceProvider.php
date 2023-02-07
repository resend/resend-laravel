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
        $this->app->singleton(Client::class, static function (): Client {
            $apiKey = config('resend.api_key');

            if (! is_string($apiKey)) {
                throw new InvalidArgumentException('API key missing');
            }

            return Resend::client($apiKey);
        });

        $this->app->alias(Client::class, 'resend');
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/resend.php', 'resend'
        );
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/resend.php' => config_path('resend.php'),
        ]);
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
