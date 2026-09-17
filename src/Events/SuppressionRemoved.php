<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuppressionRemoved
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new suppression removed event instance.
     */
    public function __construct(
        public array $payload,
        public array $headers = []
    ) {
        //
    }
}
