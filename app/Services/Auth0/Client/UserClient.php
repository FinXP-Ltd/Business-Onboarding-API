<?php

namespace App\Services\Auth0\Client;

use App\Enums\Auth0CacheKeys;
use App\Enums\UserRole;
use App\Exceptions\Auth0Exception;
use App\Mail\NewUserMail;
use App\Models\AgentCompany;
use App\Models\Auth\User;
use App\Services\Auth0\Facade\Auth0Service;
use DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Throwable;

class UserClient
{

    public function __construct(private User $users)
    {

    }

    public function getUser(User $user): ?User
    {
        if (gettype($user) === "string") {
            $user = $this->users->findOrFail($user);
        }

        $cacheId = Auth0CacheKeys::USER_PREFIX . $user->auth0;

        if (Cache::has($cacheId)) {
            return Cache::get($cacheId);
        }

        try {
            $user = Auth0Service::getUser($user->auth0);
            Cache::put($cacheId, $user, now()->addDay());
        } catch (Throwable $exception) {
            info("$user->email: not found user in client users!");
            info($exception);
        }

        return $user;
    }

    public function create(array $payload, bool $includeMail = true, bool $reInvite = false): array
    {
        $user = $payload['user'];
        $roles = $payload['roles'];
        $note = $payload['note'] ?? null;
        $company = $payload['company'] ?? null;

        $createAuth0Id = null;

        DB::beginTransaction();

        try {
            $randomPassword = 'Fxp@' . Str::random(8) . '0!';

            $auth0User = Auth0Service::createUser([
                'email' => $user['email'],
                'name' => $user['last_name'] . ', ' . $user['first_name'],
                'phone_number' => $user['phone_number'] ?? null,
                'password' => $user['password'] ?? $randomPassword,
                'blocked' => false,
                'verify_email' => false
            ]);

            $createAuth0Id = $auth0User['user_id'];

            $userPayload = [
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'auth0' => $auth0User['user_id'],
                'is_active' => true,
                'program_id' => config('kycp.program_id.customers'),
                'note' => $note
            ];

            if($reInvite) {
                $user = User::whereEmail($user['email'])->first();
                $user->update([
                    'auth0' => $auth0User['user_id']
                ]);
            } else {
                $user = $this->users->create($userPayload);

                $this->_userCreatedCompany($user, $company, $roles);
            }

            // Sync Roles to Auth0 and Application
            Auth0Service::addUserRoles($auth0User['user_id'], $this->_roleMapping($roles, false));

            $user->syncRoles($roles);

            // Cache user details
            $cacheId = Auth0CacheKeys::USER_PREFIX() . $user->auth0;

            Cache::put($cacheId, $user, now()->addDay());

            if ($includeMail) {
                // pick first role
                $userPayload['role'] = $this->_roleMapping($roles, true)[0]['description'];

                $userPayload['password'] = $randomPassword;

                $userPasswordLink = $this->getResetPasswordLink($user->auth0);

                $userPayload['url'] = $userPasswordLink;

                Mail::to($user->email)->send(new NewUserMail($userPayload));
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = __('user.create_failed');

            if ($exception instanceof Auth0Exception) {
                $code = $exception->getCode();
                $message = $exception->getMessage();
            }

            if ($createAuth0Id) {
                Auth0Service::deleteUser($createAuth0Id);
            }

            return [
                'code' => $code,
                'status' => 'error',
                'message' => $message
            ];
        }

        return [
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => __('user.created'),
            'data' => [
                'user_id' => $user->id,
                'auth0' => $createAuth0Id
            ]
        ];
    }

    public function update(string $id, array $payload)
    {
        $payloadUser = $payload['user'];
        $company = $payload['company'] ?? null;

        DB::beginTransaction();

        try {
            $user = $this->users->findOrFail($id);

            info($payloadUser);

            $auth0User = Auth0Service::updateUser($user->auth0, [
                'email' => $payloadUser['email'] ?? $user->email,
                'blocked' => isset($payloadUser['blocked']) ? (bool)($payloadUser['blocked']) : !$user->is_active,
            ]);

            $user->update([
                'email' => $payloadUser['email'] ?? $user->email,
                'is_active' => isset($payloadUser['is_active'])
                    ? (bool)($payloadUser['is_active'])
                    : $user->is_active
            ]);

            $this->_userUpdatedCompany($user, $company);

            // Cache user data
            $cacheId = Auth0CacheKeys::USER_PREFIX() . $auth0User['user_id'];
            Cache::put($cacheId, $user, now()->addDay());

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = __('user.update_failed');

            if ($exception instanceof Auth0Exception) {
                $code = $exception->getCode();
                $message = $exception->getMessage();
            }

            return [
                'code' => $code,
                'status' => 'error',
                'message' => $message
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __('user.updated')
        ];
    }

    public function resendInvitation(string $userId): array
    {
        try {
            $user = $this->users->findOrFail($userId);

            $userPasswordLink = $this->getResetPasswordLink($user->auth0);

            $userPayload = [
                'url' => $userPasswordLink,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ];

            $userPayload['url'] = $userPasswordLink;

            Mail::to($user->email)->send(new NewUserMail($userPayload));
        } catch (Throwable $exception) {
            info($exception);

            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'status' => 'error',
                'message' => 'Unable to send resend invitation!'
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Successfully sent invitation!'
        ];
    }

    public function getRoles(): array
    {
        $cacheName = 'AUTH0_ROLES';

        if (Cache::has('AUTH0_ROLES')) {
            return Cache::get('AUTH0_ROLES');
        }

        $roles = Auth0Service::getRoles();
        Cache::put($cacheName, $roles);

        return $roles;
    }

    public function hasRoles(): bool
    {
        $roles = Auth0Service::getRole(auth()->id());

        $roleResource = Arr::pluck($roles,'name');

        if($roleResource){
            return true;
        }

       return false;
    }

    public function getResetPasswordLink(string $userId): string
    {
        $dataPayload =  [
            'client_id' => config('auth0.guards.default.clientId'),
            'user_id' => $userId,
            'ttl_sec' => 2678400
        ];

        $data = Auth0Service::generateResetPasswordLink($dataPayload);

        return $data['ticket'];
    }

    private function _roleMapping(array $roles, bool $all = false): array
    {
        $auth0Roles = collect($this->getRoles());

        return array_map(function ($role) use ($auth0Roles, $all) {
            $dataRole = $auth0Roles->first(function ($auth0Role) use ($role) {
                return $auth0Role['name'] === $role;
            });

            if ($all) {
                return $dataRole;
            }

            return $dataRole
                ? $dataRole['id']
                : null;
        }, $roles);
    }

    private function _userCreatedCompany(User $user, ?string $company, array $roles): void
    {
        if ($company && in_array(UserRole::AGENT(), $roles)) {
            $company = request()->input('company', null);

            $agentCompany = AgentCompany::findOrFail($company);

            $agentCompany->users()->attach($user->id);
        }
    }

    private function _userUpdatedCompany(User $user, ?string $company): void
    {
        if ($company && $user->hasRole(UserRole::AGENT())) {

            $company = request()->input('company', null);

            $agentCompany = AgentCompany::findOrFail($company);

            $userCurrentCompany = $user->agentCompanies()->first();

            if ($userCurrentCompany) {
                $user->agentCompanies()->detach($userCurrentCompany->id);
            }

            $agentCompany->users()->attach($user->id);
        }
    }
}
