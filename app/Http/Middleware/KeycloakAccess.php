<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;

class KeycloakAccess
{
    public function handle(Request $request, Closure $next, $parameter)
    {
        if (!(Auth::hasRole($parameter, 'member')
            || Auth::hasRole($parameter, 'admin')
            || Auth::hasRole($parameter, 'better_payment')
            || Auth::hasRole($parameter, 'customers'))) {
            throw new AuthorizationException('You don\'t have enough access.');
        }

        return $next($request);
    }
}
