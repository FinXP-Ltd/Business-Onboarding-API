<?php

namespace App\Traits\Services\BusinessCorporate;

use Throwable;
use App\Enums\UserRole;
use App\Models\Auth\User;
use App\Models\Business;
use Illuminate\Http\Response;
use App\Exceptions\Auth0Exception;
use App\Mail\SharedApplicationMail;
use App\Services\Auth0\Facade\Auth0Service;
use App\Mail\NewUserMail;
use Illuminate\Support\Str;
use App\Models\SharedApplication;
use App\Models\ShareInvites;
use App\Services\Auth0\Facade\UserService;
use App\Services\LocalUser\Facades\LocalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

trait InvitationAndSharing
{
    public function inviteClient($payload, $role = 'invited client')
    {
        $response = null;

        $userId = null;

        $user  = User::whereEmail($payload['email'])->first();

        if ($user) {
            return [
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => 'User already invited!'
            ];
        }

        $businessId = $payload['business_id'] ?? null;

        unset($payload['business_id']);

        $userId = $user?->id ?? null;

        $createdAuth0Id = $user?->auth0 ?? null;

        $createdAuth0Id = $user->auth0 ?? null;

        $userPayload = [
            'user' => [
                'email' => $payload['email'],
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'password' =>  'Fxp@' . Str::random(8) . '0!',
                'phone_number' => null,
                'new' => false
            ],
            'roles' => [
                $role //invited by the agent, role will be this default
            ]
        ];

        $createdAuth0Id = null;

        DB::beginTransaction();

        try {
            if(!$user){
                $userPayload['user']['new'] = true;
                $response = UserService::create($userPayload, false);
                $userId = $response['data']['user_id'];

                $createdAuth0Id = $response['data']['auth0'];
            }

            if (!empty($response) && $response['code'] !== Response::HTTP_CREATED) {
                throw new Auth0Exception($response['message'], $response['code']);
            }

            $payload['client_id'] = $userId;

            ShareInvites::updateOrCreate($payload);

            if (! $businessId) {
                Mail::to($payload['email'])->send(new NewUserMail([
                    'first_name' => $payload['first_name'],
                    'last_name' => $payload['last_name'],
                    'email' => $payload['email'],
                    'password' => 'Fxp@' . Str::random(8) . '0!',
                    'url' => UserService::getResetPasswordLink($createdAuth0Id)
                ]));
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            if ($createdAuth0Id) {
                Auth0Service::deleteUser($createdAuth0Id);
            }

            return [
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => 'Unable to invite user!'
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Invitation sent successfully!'
        ];
    }

    public function shareApplication(array $payload): array
    {
        $parentUser = LocalUser::createOrFetchLocalUser();

        $role = $parentUser->roles()->first()->name;

        $user = User::whereEmail($payload['email'])->first();

        if ($user) {
            $sharedApplicationExists = SharedApplication::where([
                'parent_id' => $parentUser->id,
                'business_id' => $payload['business_id'],
                'user_id' => $user->id
            ])->exists();

            if ($sharedApplicationExists) {
                return [
                    'code' => Response::HTTP_CONFLICT,
                    'status' => 'failed',
                    'message' => 'You have already shared the application with this user!'
                ];
            }
        }

        $isFirstTimeUser = false;

        DB::beginTransaction();

        try {

            if (! $user) {
                $inviteClient = $this->inviteClient($payload, $this->invitedRole($role));

                if ($inviteClient['code'] === Response::HTTP_CONFLICT) {
                    return $inviteClient;
                }

                $user = User::whereEmail($payload['email'])->first();

                $isFirstTimeUser = true;
            }

            SharedApplication::create([
                'parent_id' => $parentUser->id,
                'business_id' => $payload['business_id'],
                'user_id' => $user->id
            ]);

            $business = Business::findOrFail($payload['business_id']);

            $company = $business->companyInformation()->first();

            $mailPayload = array_merge($payload, [
                'company_name' => $company?->company_name,
                'url' => $isFirstTimeUser
                    ? UserService::getResetPasswordLink($user->auth0)
                    : config('app.asset_url')
            ]);

            Mail::to($payload['email'])
                ->send(new SharedApplicationMail($mailPayload));

            DB::commit();

        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'status' => 'failed',
                'message' => 'Unable to share application!'
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Successfully Shared Application!',
        ];
    }

    private function invitedRole(string $role): string
    {
        $toUseRole = UserRole::INVITED_CLIENT();

        switch($role) {
            case UserRole::AGENT():
                $toUseRole = UserRole::INVITED_CLIENT();
                break;

            case UserRole::INVITED_CLIENT():
                $toUseRole = UserRole::INVITED_CLIENT();
                break;

            case UserRole::OPERATION():
                $toUseRole = UserRole::CLIENT();
                break;

                case UserRole::CLIENT():
                $toUseRole = UserRole::CLIENT();
                break;

                default:
                break;
        }

        return $toUseRole;
    }
}
