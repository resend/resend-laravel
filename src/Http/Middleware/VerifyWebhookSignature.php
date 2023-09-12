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
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $headers = collect($request->headers->all())->transform(function ($item) {
                return $item[0];
            })->all();

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
}
