<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new contact deleted event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
