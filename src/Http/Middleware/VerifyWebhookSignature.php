<?php

namespace Resend\Laravel\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Resend\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $headers = $this->getTransformedHeaders($request);

            WebhookSignature::verify(
                $request->getContent(),
                $headers,
                config('resend.webhook.secret'),
                config('resend.webhook.tolerance')
            );
        } catch (Exception $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }

    /**
     * Transform headers to a simple associative array.
     * This method extracts the first value from each header and returns an array where each key is the header name and the associated value is that first header value
     */
    protected function getTransformedHeaders(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = $value[0];
        }

        return $headers;
    }
}
