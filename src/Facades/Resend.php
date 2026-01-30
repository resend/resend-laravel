<?php

namespace Resend\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Provides Resend integration for Laravel and Symfony Mailer.
 *
 * @method static \Resend\Service\ApiKey apiKeys()
 * @method static \Resend\Service\Audience audiences()
 * @method static \Resend\Service\Batch batch()
 * @method static \Resend\Service\Broadcast broadcasts()
 * @method static \Resend\Service\Contact contacts()
 * @method static \Resend\Service\ContactProperty contactProperties()
 * @method static \Resend\Service\Domain domains()
 * @method static \Resend\Service\Email emails()
 * @method static \Resend\Service\Segment segments()
 * @method static \Resend\Service\Template templates()
 * @method static \Resend\Service\Topic topics()
 * @method static \Resend\Service\Webhook webhooks()
 *
 * @see \Resend\Client
 */
class Resend extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'resend';
    }
}
