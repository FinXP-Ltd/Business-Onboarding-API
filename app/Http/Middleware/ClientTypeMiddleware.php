<?php

namespace App\Http\Middleware;

use App\Enums\ClientType;
use Closure;
use Illuminate\Http\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ClientTypeMiddleware
{
    public function handle($request, Closure $next, $clienType)
    {
        $tokenClientType = getClientType();

        $types = explode('|', $clienType);

        if (in_array($tokenClientType, $types)) {
            return $next($request);
        }

        throw new UnauthorizedException(Response::HTTP_FORBIDDEN, 'Unauthorized user!');
    }
}
