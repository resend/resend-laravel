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

        $headers = [];
        $headersToBypass = ['from', 'to', 'cc', 'bcc', 'subject', 'content-type', 'sender', 'reply-to'];
        foreach ($email->getHeaders()->all() as $name => $header) {
            if (in_array($name, $headersToBypass, true)) {
                continue;
            }

            $headers[$header->getName()] = $header->getBodyAsString();
        }

        $attachments = [];
        if ($email->getAttachments()) {
            foreach ($email->getAttachments() as $attachment) {
                $headers = $attachment->getPreparedHeaders();
                $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

                $item = [
                    'content' => str_replace("\r\n", '', $attachment->bodyToString()),
                    'filename' => $filename,
                ];

                $attachments[] = $item;
            }
        }

        try {
            $result = $this->resend->emails->send([
                'bcc' => $this->stringifyAddresses($email->getBcc()),
                'cc' => $this->stringifyAddresses($email->getCc()),
                'from' => $envelope->getSender()->toString(),
                'headers' => $headers,
                'html' => $email->getHtmlBody(),
                'reply_to' => $this->stringifyAddresses($email->getReplyTo()),
                'subject' => $email->getSubject(),
                'text' => $email->getTextBody(),
                'to' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
                'attachments' => $attachments,
            ]);
        } catch (Exception $exception) {
            throw new Exception(
                $exception->getMessage(),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception
            );
        }

        $messageId = $result->id;

        $email->getHeaders()->addHeader('X-Resend-Email-ID', $messageId);
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
