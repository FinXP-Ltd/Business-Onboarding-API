<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class PortalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $credentials = [$request->getUser(), $request->getPassword()];

        if (!collect(config('portal.server_credentials'))->contains($credentials)) {
           throw new AuthenticationException('Unauthorized');
        }

        return $next($request);
    }
}
