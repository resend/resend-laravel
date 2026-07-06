<?php

namespace Resend\Laravel\Tests;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;

class RegisterRoutes extends TestCase
{
    protected function disableRoutes($app): void
    {
        $app['config']->set('resend.routes', false);
    }

    public function test_the_webhook_route_is_registered_by_default(): void
    {
        $this->assertTrue(Route::has('resend.webhook'));

        // 400: the unsigned request reached the webhook signature middleware
        $this->post('resend/webhook')->assertStatus(400);
    }

    #[DefineEnvironment('disableRoutes')]
    public function test_routes_are_not_registered_when_disabled(): void
    {
        $this->assertFalse(Route::has('resend.webhook'));

        $this->post('resend/webhook')->assertNotFound();
    }
}
