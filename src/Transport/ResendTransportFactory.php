<?php

namespace Resend\Laravel\Transport;

use Resend\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class ResendTransportFactory extends AbstractTransport
{
    public function __construct(
        protected Client $resend,
        protected array $config = []
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
