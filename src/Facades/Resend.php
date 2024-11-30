<?php

namespace Resend\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Resend\Service\ApiKey;
use Resend\Service\Audience;
use Resend\Service\Batch;
use Resend\Service\Contact;
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

    public static function contacts(): Contact
    {
        return static::getFacadeRoot()->contacts;
    }

    public static function audiences(): Audience
    {
        return static::getFacadeRoot()->audiences;
    }
    public static function batch(): Batch
    {
        return static::getFacadeRoot()->batch;
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'resend';
    }
}
