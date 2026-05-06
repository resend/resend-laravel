<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSuppressed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email suppressed event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
