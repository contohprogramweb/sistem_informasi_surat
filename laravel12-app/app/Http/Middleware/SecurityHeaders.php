<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP)
        // Allow resources from self, CDN providers, and data URIs for images
        $cspPolicy = "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://cdn.jsdelivr.net/npm/chart.js@4.3.0; " .
                    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.datatables.net https://fonts.googleapis.com; " .
                    "font-src 'self' https://fonts.gstatic.com; " .
                    "img-src 'self' data: https:; " .
                    "connect-src 'self' https:; " .
                    "frame-ancestors 'self'; " .
                    "base-uri 'self'; " .
                    "form-action 'self'";

        $response->headers->set('Content-Security-Policy', $cspPolicy);

        // HTTP Strict Transport Security (HSTS) - only if HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Permissions Policy (formerly Feature Policy)
        $permissionsPolicy = "geolocation=(), microphone=(), camera=(), payment=(), usb=()";
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Remove X-Powered-By header
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
