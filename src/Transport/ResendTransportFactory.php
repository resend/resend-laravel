<?php

namespace Resend\Laravel\Transport;

use Exception;
use Resend\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ResendTransportFactory extends AbstractTransport
{
    /**
     * Create a new Resend transport instance.
     */
    public function __construct(
        protected Client $resend,
        protected array $config = []
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        try {
            $result = $this->resend->sendEmail([
                'from' => $email->getFrom(),
                'to' => $email->getTo(),
                'subject' => $email->getSubject(),
                'text' => $email->getTextBody(),
                'html' => $email->getHtmlBody(),
            ]);
        } catch (Exception $e) {
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'resend';
    }
}
