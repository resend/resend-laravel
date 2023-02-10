<?php

namespace Resend\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static Resend\Responses\Email\EmailSent sendEmail(array $parameters)
 */
final class Resend extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'resend';
    }
}
