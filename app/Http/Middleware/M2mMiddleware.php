<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class M2mMiddleware
{
    public function handle($request, Closure $next)
    {
        $tokenClientType = getAuthorizationType();

        if ($tokenClientType === 'client-credentials') {
            return $next($request);
        }

        throw new UnauthorizedException(Response::HTTP_FORBIDDEN, 'Unauthorized user!');
    }
}
