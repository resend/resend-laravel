<?php

namespace Resend\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Resend\Laravel\ResendServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ResendServiceProvider::class,
        ];
    }
}
