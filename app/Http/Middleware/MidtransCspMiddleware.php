<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MidtransCspMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('[CSP] Applying Midtrans CSP Header to: '.$request->fullUrl());
        $response = $next($request);

        // Aggressively clear ALL possible CSP header variations
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');
        $response->headers->remove('content-security-policy');
        $response->headers->remove('x-content-security-policy');

        // Refined CSP: EXPLICIT MIDTRANS & EVAL SUPPORT
        $csp = "default-src * 'unsafe-inline' 'unsafe-eval' data: blob: http: https:; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'unsafe-hashes' * http: https: https://app.midtrans.com https://app.sandbox.midtrans.com https://snap-popup-app.sandbox.midtrans.com https://snap-popup-app.midtrans.com *.midtrans.com *.google.com *.googleapis.com; ";
        $csp .= "script-src-elem 'self' 'unsafe-inline' 'unsafe-eval' * http: https: https://app.midtrans.com https://app.sandbox.midtrans.com https://snap-popup-app.sandbox.midtrans.com https://snap-popup-app.midtrans.com *.midtrans.com; ";
        $csp .= "script-src-attr 'self' 'unsafe-inline' 'unsafe-eval' * http: https:; ";
        $csp .= "connect-src 'self' * http: https: https://app.midtrans.com https://app.sandbox.midtrans.com *.midtrans.com; ";
        $csp .= "img-src 'self' * data: blob: http: https: *.midtrans.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' * http: https:; ";
        $csp .= "font-src 'self' * data: http: https:; ";
        $csp .= "frame-src 'self' * http: https: https://app.midtrans.com https://app.sandbox.midtrans.com https://snap-popup-app.sandbox.midtrans.com https://snap-popup-app.midtrans.com *.midtrans.com; ";
        $csp .= "child-src 'self' * http: https:; ";
        $csp .= "worker-src 'self' * blob:; ";
        $csp .= "object-src 'none'; ";

        // Force set the header
        $response->headers->set('Content-Security-Policy', $csp, true);
        $response->headers->set('X-Content-Security-Policy', $csp, true);

        // Log ALL final headers to verify what the browser actually sees
        Log::info('[CSP] FINAL RESPONSE HEADERS: ', $response->headers->all());

        return $response;
    }
}
