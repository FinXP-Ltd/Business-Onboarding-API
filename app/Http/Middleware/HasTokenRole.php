<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class HasTokenRole
{
    const ERROR_STATUS = "FAILED";
    const ERROR_MESSAGE = "role.error";

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (hasAuth0AnyRole($role)) {
            return $next($request);
        }

        return response()->json([
            'status' => self::ERROR_STATUS,
            'message' => __(self::ERROR_MESSAGE),
            'code' => Response::HTTP_UNAUTHORIZED,
        ], Response::HTTP_UNAUTHORIZED);
    }
}
