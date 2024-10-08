<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('Expect-Ct', 'max-age=0');
        $response->headers->set('Permissions-Policy', 'autoplay=()');
        $response->headers->set('Cache-control', 'no-store');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('content-security-policy', "script-src 'self' https://api.finxp.com 'unsafe-inline' 'unsafe-eval'");
        return $response;
    }
}
