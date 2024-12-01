<?php

namespace Resend\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Resend\Service\ApiKey;
use Resend\Service\Audience;
use Resend\Service\Batch;
use Resend\Service\Contact;
use Resend\Service\Domain;
use Resend\Service\Email;

/**
 * Provides Resend integration for Laravel and Symfony Mailer.
 *
 * @method static Contact contacts() Manage Resend contacts.
 * @method static Audience audiences() Manage Resend audiences through the Resend Email API.
 * @method static Batch batch() Create and send Resend Batches.
 *
 * @package resend-laravel
 * @see <a href="https://resend.com/docs/introduction">Resend Docs</a>
 * @see <a href="https://resend.com/docs/api-reference/introduction">API Reference</a>
 */



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
