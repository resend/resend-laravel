<?php

namespace Resend\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Resend\Service\ApiKey;
use Resend\Service\Domain;
use Resend\Service\Email;

class Resend extends Facade
{
    public static function apiKeys(): ApiKey
    {
        return static::getFacadeRoot()->apiKeys;
    }

    public static function domains(): Domain
    {
        return static::getFacadeRoot()->domains;
    }

    public static function emails(): Email
    {
        return static::getFacadeRoot()->emails;
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'resend';
    }
}
