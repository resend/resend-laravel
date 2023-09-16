<?php

namespace Resend\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailDeliveryDelayed;
use Resend\Laravel\Events\EmailOpened;
use Resend\Laravel\Events\EmailSent;
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
        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload);

            return $response;
        }

        return $this->missingMethod($payload);
    }

    /**
     * Handle email bounced event.
     */
    protected function handleEmailBounced(array $payload): Response
    {
        EmailBounced::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email clicked event.
     */
    protected function handleEmailClicked(array $payload): Response
    {
        EmailClicked::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email complained event.
     */
    protected function handleEmailComplained(array $payload): Response
    {
        EmailComplained::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email delivered event.
     */
    protected function handleEmailDelivered(array $payload): Response
    {
        EmailDelivered::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email delivery delayed event.
     */
    protected function handleEmailDeliveryDelayed(array $payload): Response
    {
        EmailDeliveryDelayed::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email opened event.
     */
    protected function handleEmailOpened(array $payload): Response
    {
        EmailOpened::dispatch($payload);

        return $this->successMethod();
    }

    /**
     * Handle email sent event.
     */
    protected function handleEmailSent(array $payload): Response
    {
        EmailSent::dispatch($payload);

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
