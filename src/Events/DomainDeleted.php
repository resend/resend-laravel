<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new domain deleted event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
