<?php

namespace Resend\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Resend\Laravel\Events\WebhookHandled;
use Resend\Laravel\Events\WebhookReceived;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Handle a Resend webhook call.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));
        dump($method);

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload);

            WebhookHandled::dispatch($payload);

            return $response;
        }

        return $this->missingMethod($payload);
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
