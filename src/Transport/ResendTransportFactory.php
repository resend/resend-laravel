<?php

namespace Resend\Laravel\Transport;

use Exception;
use Resend\Contracts\Client;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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
        $envelope = $message->getEnvelope();

        try {
            $result = $this->resend->emails->send([
                'from' => $envelope->getSender()->toString(),
                'to' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
                'subject' => $email->getSubject(),
                'bcc' => $this->stringifyAddresses($email->getBcc()),
                'cc' => $this->stringifyAddresses($email->getCc()),
                'reply_to' => $this->stringifyAddresses($email->getReplyTo()),
                'text' => $email->getTextBody(),
                'html' => $email->getHtmlBody(),
            ]);
        } catch (Exception $exception) {
            throw new Exception(
                $exception->getMessage(),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception
            );
        }
    }

    /**
     * Get the recipients without CC or BCC.
     */
    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        return array_filter($envelope->getRecipients(), function (Address $address) use ($email) {
            return in_array($address, array_merge($email->getCc(), $email->getBcc()), true) === false;
        });
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'resend';
    }
}
