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
 * @method static Contact contacts() Manage Resend contacts. <a href="https://resend.com/docs/dashboard/audiences/contacts">Contacts Docs</a>
 * @method static Audience audiences() Manage Resend audiences. <a href="https://resend.com/docs/dashboard/audiences">Audience Docs</a>
 * @method static Batch batch() Create and send Resend Batches.
 * ...
 * @see <a href="https://resend.com/docs/introduction">Resend Docs</a>
 * @package resend-laravel
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
