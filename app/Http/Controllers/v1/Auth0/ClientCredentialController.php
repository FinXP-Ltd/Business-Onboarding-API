<?php

namespace App\Http\Controllers\v1\Auth0;

use App\Abstracts\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ClientCredentialController extends BaseController
{
    protected string $authUrl;

    public function __construct()
    {
        $domain = config('auth0.guards.default.domain');
        $this->authUrl = "$domain/oauth/token";
    }

    public function generateToken(): JsonResponse
    {
        $payload = array_merge(
            $this->_prepareCredentials(),
            request()->only(['client_id', 'client_secret'])
        );

        $response = Http::accept('application/json')
            ->asForm()
            ->post($this->authUrl, $payload);


        return $response->successful()
            ?   $this->_returnResponse($response->json())
            :   $this->response(
                    [
                        'message' => 'Unable to login!',
                        'status' => 'failed',
                        'data' => $response->json()
                    ],
                    Response::HTTP_FORBIDDEN
                );
    }

    private function _prepareCredentials(): array
    {
        $audience = config('auth0.guards.default.audience');

        return [
            'client_id' => null,

            'client_secret' => null,

            'grant_type' => 'client_credentials',

            'audience' => is_array($audience)
                ? $audience[0]
                : $audience
        ];
    }

    private function _returnResponse(array $data): JsonResponse
    {
        unset($data['scope']);

        return response()->json($data);
    }
}
