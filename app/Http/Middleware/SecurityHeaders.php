<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add basic security headers to improve Lighthouse Best Practices.
     *
     * Notes:
     * - HTTPS/HSTS cannot be fully satisfied on plain http:// (e.g., localhost).
     * - CSP here is intentionally compatible with current inline styles and Alpine usage.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Avoid touching binary/streamed responses.
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

            // HSTS only makes sense over HTTPS.
            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }

            // CSP: keep it compatible with current templates (inline styles, Alpine, and CDN assets).
            // This is a pragmatic baseline; making it "strict" would require removing inline styles and switching Alpine to a CSP build.
            $csp = implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "frame-ancestors 'none'",
                "object-src 'none'",
                "img-src 'self' data: https:",
                "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
                // Alpine (CDN build) relies on eval; keep for compatibility.
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com http://localhost:* http://127.0.0.1:*",
                "connect-src 'self' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
