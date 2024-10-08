<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestrictQueryParams
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
        if ($request->query->count() > 0) {
            return response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Query parameters are not allowed',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
