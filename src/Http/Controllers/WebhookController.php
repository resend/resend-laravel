<?php

namespace Resend\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Resend\Laravel\Events\ContactCreated;
use Resend\Laravel\Events\ContactDeleted;
use Resend\Laravel\Events\ContactUpdated;
use Resend\Laravel\Events\DomainCreated;
use Resend\Laravel\Events\DomainDeleted;
use Resend\Laravel\Events\DomainUpdated;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailDeliveryDelayed;
use Resend\Laravel\Events\EmailFailed;
use Resend\Laravel\Events\EmailOpened;
use Resend\Laravel\Events\EmailReceived;
use Resend\Laravel\Events\EmailSent;
use Resend\Laravel\Events\EmailSuppressed;
use Resend\Laravel\Http\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Create a new webhook controller instance.
     */
    public function __construct()
    {
        if (config('resend.webhook.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle a Resend webhook call.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (is_null($payload) || ! isset($payload['type'])) {
            return new Response('Invalid payload', 400);
        }

        $headers = $this->extractHeaders($request);

        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload, $headers);

            return $response;
        }

        return $this->missingMethod($payload);
    }

    /**
     * Extract the Svix webhook headers from the request so listeners can use
     * them (e.g. `svix-id` as a deduplication key).
     *
     * @return array<string, string>
     */
    protected function extractHeaders(Request $request): array
    {
        return collect(['svix-id', 'svix-timestamp', 'svix-signature'])
            ->mapWithKeys(fn (string $key) => [$key => $request->header($key)])
            ->filter()
            ->all();
    }

    /**
     * Handle contact created event.
     */
    protected function handleContactCreated(array $payload, array $headers = []): Response
    {
        ContactCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle contact deleted event.
     */
    protected function handleContactDeleted(array $payload, array $headers = []): Response
    {
        ContactDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle contact updated event.
     */
    protected function handleContactUpdated(array $payload, array $headers = []): Response
    {
        ContactUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain created event.
     */
    protected function handleDomainCreated(array $payload, array $headers = []): Response
    {
        DomainCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain deleted event.
     */
    protected function handleDomainDeleted(array $payload, array $headers = []): Response
    {
        DomainDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain updated event.
     */
    protected function handleDomainUpdated(array $payload, array $headers = []): Response
    {
        DomainUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email bounced event.
     */
    protected function handleEmailBounced(array $payload, array $headers = []): Response
    {
        EmailBounced::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email clicked event.
     */
    protected function handleEmailClicked(array $payload, array $headers = []): Response
    {
        EmailClicked::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email complained event.
     */
    protected function handleEmailComplained(array $payload, array $headers = []): Response
    {
        EmailComplained::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email delivered event.
     */
    protected function handleEmailDelivered(array $payload, array $headers = []): Response
    {
        EmailDelivered::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email delivery delayed event.
     */
    protected function handleEmailDeliveryDelayed(array $payload, array $headers = []): Response
    {
        EmailDeliveryDelayed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email opened event.
     */
    protected function handleEmailOpened(array $payload, array $headers = []): Response
    {
        EmailOpened::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email sent event.
     */
    protected function handleEmailSent(array $payload, array $headers = []): Response
    {
        EmailSent::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email failed event.
     */
    protected function handleEmailFailed(array $payload, array $headers = []): Response
    {
        EmailFailed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email suppressed event.
     */
    protected function handleEmailSuppressed(array $payload, array $headers = []): Response
    {
        EmailSuppressed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email received event.
     */
    protected function handleEmailReceived(array $payload, array $headers = []): Response
    {
        EmailReceived::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     */
    protected function successMethod($parameters = []): Response
    {
        return new Response('Webhook handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     */
    protected function missingMethod($parameters = []): Response
    {
        return new Response;
    }
}
