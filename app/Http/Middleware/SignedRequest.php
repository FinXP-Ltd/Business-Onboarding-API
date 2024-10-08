<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;


class SignedRequest
{
    /**
     * Verify if the request is signed
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {

        if($request->has('corporate_saving') && $request->corporate_saving && !$request->has('has_file')) {
            $secretKey = config('app.saving_secret');

            if (!$request->hasHeader('x-app-ts') || !$request->hasHeader('x-app-guid') || !$request->hasHeader('x-app-digest')) {
                return response()->json([], Response::HTTP_NO_CONTENT);
            }

            $ts = $request->header('x-app-ts');
            $guid = $request->header('x-app-guid');
            $digest = $request->header('x-app-digest');

            $signature = $ts .  $guid;

            if (count($request->all()) > 0) {
                $signature = $signature . json_encode($request->all(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }

            $calculatedDigest = hash_hmac('sha256', $signature, $secretKey);

            if (!hash_equals($calculatedDigest, $digest)) {
                return response()->json([], Response::HTTP_NO_CONTENT);
            }
        }

        return $next($request);
    }
}
