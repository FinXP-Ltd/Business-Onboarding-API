<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;

class DomainCheckMiddleware
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
        $allowedHosts = explode(',', env('ALLOWED_DOMAINS'));

        $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);

        if(!app()->runningUnitTests()) {
            if(!\in_array($requestHost, $allowedHosts, false)) {
                throw new SuspiciousOperationException(_('error.origin.not_allowed'));
            }
        }

        return $next($request);
    }
}
