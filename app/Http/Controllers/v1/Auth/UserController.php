<?php

namespace App\Http\Controllers\v1\Auth;

use App\Abstracts\Controller as BaseController;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\PasswordReset;
use App\Services\Keycloak\Facades\KeycloakRequest;
use App\Services\Keycloak\Facades\KeycloakService;
use Auth0\Laravel\Facade\Auth0;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

use Symfony\Component\HttpFoundation\Cookie;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserController extends BaseController
{

    protected string $baseUrl;

    protected string $realm;

    protected string $authUrl;

    public function __construct()
    {
        $this->baseUrl = '';
        $this->realm = '';
        $this->authUrl = "$this->baseUrl/auth/realms/$this->realm/protocol/openid-connect/token";
    }

    public function show(): JsonResponse
    {
        $user = auth()->id();

        $profile = cache()->get($user);

        if (null === $profile) {
            $endpoint = Auth0::management()->users();
            $profile = $endpoint->get($user);

            $profile = Auth0::json($profile);

            cache()->put($user, $profile, 120);
        }

        return response()->json($profile);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $userInfo = KeycloakService::getUsersByEmail($email);

        if (!$userInfo) {
            return response()->json([
                'code' => Response::HTTP_NOT_FOUND,
                'status' => 'failed',
                'message' =>  __('user.invalid_email'),
            ], Response::HTTP_NOT_FOUND);
        }

        $userEmail = $userInfo['email'];

        $token = PasswordReset::generateToken();
        $expiration = PasswordReset::calculateExpirationTime();

        PasswordReset::saveToken($email, $token, $expiration);

        PasswordReset::sendResetPasswordEmail($userEmail, $token, $email);

        return response()->json(['message' => __('user.password_reset')], Response::HTTP_OK);
    }
    public function validateToken(Request $request, $token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => __('user.password_token.token_invalid')
            ], Response::HTTP_BAD_REQUEST);
        }

        $tokenExpiration = $passwordReset->created_at;

        if (Carbon::now()->diffInMinutes($tokenExpiration) > 60) {
            return response()->json(['message' => __('user.password_token.token_expired')], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['token' => $token], Response::HTTP_OK);
    }

    public function changePassword(PasswordResetRequest $request): JsonResponse
    {
        $password = $request->only(['password', 'password_confirmation', 'token']);
        $email = $this->getEmailFromToken($password['token']);

        $passwordReset = PasswordReset::where('token', $password['token'])->first();

        $response = null;

        switch (true) {
            case !$passwordReset:
                $response = ['message' => __('user.password_token.token_invalid')];
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case Carbon::now()->diffInMinutes($passwordReset->created_at) > 60:
                $response = ['message' => __('user.password_token.token_expired')];
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            default:
                $user = KeycloakService::getUsersByEmail($email);
                if (!$user) {
                    $response = ['message' => 'User not found'];
                    $statusCode = Response::HTTP_NOT_FOUND;
                } else {
                    $userId = $user['id'];
                    $response = KeycloakService::changePassword($userId, $password);
                    $statusCode = $response['code'];
                    PasswordReset::passwordConfirmationEmail($email);
                }
                break;
        }

        return response()->json($response, $statusCode);
    }
    public function login(AuthRequest $request): JsonResponse
    {
        $payload = array_merge($this->_prepareCredentials(),
            $request->only(['username', 'password'])
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

    public function attemptRefresh(): JsonResponse
    {
        $refreshToken = request()->cookie('fxp-refresh-token');

        $payload = $this->_prepareCredentials();

        $payload['grant_type'] = 'refresh_token';

        $payload['refresh_token'] = $refreshToken;

        $response = Http::accept('application/json')
            ->asForm()
            ->post($this->authUrl, $payload);

        $responseData = $response->json();

        return $response->successful() && isset($responseData['access_token'])
            ?   $this->_returnResponse($response->json())
            :   $this->response(
                    [
                        'message' => 'Unable to request refresh token!',
                        'status' => 'failed',
                        'data' => $response->json()
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
    }

    public function create(UserRequest $request): JsonResponse
    {
        $response = KeycloakService::create($request->all());
        return $this->response($response, $response['code']);
    }

    public function update(UserRequest $request, string $userId): JsonResponse
    {
        $response = KeycloakService::update($userId, $request->all());
        return $this->response($response, $response['code']);
    }

    public function clearRefreshCookie(): JsonResponse
    {
        $cookie = cookie(
            'fxp-refresh-token',
            null,
            -1,
            null,
            config('keycloak.auth_origin'),
            true,
            true,
            false,
            'none'
        );

        return response()->json([
            'message' => 'Clear Session',
            'status' => true
        ])->withCookie($cookie);
    }

    private function _setCookie(array $data): Cookie
    {
        return cookie(
            'fxp-refresh-token',
            $data['refresh_token'],
            $data['refresh_expires_in'] / 60,
            null,
            config('keycloak.auth_origin'),
            true,
            true,
            false,
            'none'
        );
    }

    private function _returnResponse(array $data): JsonResponse
    {
        $cookie = $this->_setCookie($data);

        unset($data['refresh_token']);
        unset($data['refresh_expires_in']);

        return response()
                ->json($data)
                ->withCookie($cookie);
    }

    private function _prepareCredentials(): array
    {
        return [
            'client_id' => config('keycloak.client_name', null),

            'client_secret' => config('keycloak.client_secret', null),

            'grant_type' => config('keycloak.grant_type', null),

            'scope' => config('keycloak.client_scope', 'openid')
        ];
    }

    private function getEmailFromToken(string $token): ?string
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if ($passwordReset) {
            return $passwordReset->email;
        }

        return null;
    }
}
