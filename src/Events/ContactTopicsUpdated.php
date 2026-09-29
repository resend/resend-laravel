<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactTopicsUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new contact topics updated event instance.
     */
    public function __construct(
        public array $payload,
        public array $headers = []
    ) {
        //
    }
}
