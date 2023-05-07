<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email sent event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
