<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InboxThreadFolderUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new inbox thread folder updated event instance.
     */
    public function __construct(
        public array $payload,
        public array $headers = []
    ) {
        //
    }
}
