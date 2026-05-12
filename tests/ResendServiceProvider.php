<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Resend\Client;
use Resend\Laravel\Exceptions\ApiKeyIsMissing;
use Resend\Laravel\ResendServiceProvider;

it('requires an API key', function () {
    app()->get('resend');
})->throws(ApiKeyIsMissing::class);

it('can bind the Resend Client using the service config', function () {
    $app = app();
    $app->get('config')->set('services', [
        'resend' => [
            'key' => 're_test',
        ],
    ]);

    $resend = $app->get('resend');

    expect($resend)->toBeInstanceOf(Client::class);
});

it('provides', function () {
    $provider = app()->resolveProvider(ResendServiceProvider::class);

    $provides = $provider->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});

it('registers the webhook route by default', function () {
    expect(Route::has('resend.webhook'))->toBeTrue();
});

it('does not register the webhook route when register_route is false', function () {
    config()->set('resend.register_route', false);

    $router = app('router');
    (new ReflectionProperty($router, 'routes'))->setValue($router, new RouteCollection());

    $provider = new ResendServiceProvider(app());
    (new ReflectionMethod($provider, 'registerRoutes'))->invoke($provider);

    expect(Route::has('resend.webhook'))->toBeFalse();
});
