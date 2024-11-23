<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new domain updated event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
