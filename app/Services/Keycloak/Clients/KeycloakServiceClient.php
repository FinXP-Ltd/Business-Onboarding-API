<?php

namespace App\Services\Keycloak\Clients;

use DB;
use Throwable;

use App\Models\User;
use App\Events\Auth\UserCreated;
use App\Events\Auth\UserUpdated;
use App\Events\Auth\UserDeleted;
use App\Events\Auth\UserRestored;

use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Keycloak\Facades\KeycloakRequest as Keycloak;
use App\Exceptions\KeycloakException;

class KeycloakServiceClient
{
    public static $trialCount = 0;

    public function getUsersByEmail(string $email): array
    {
        $users = Keycloak::getUsers((['email' => $email]));
        return !empty($users) ? $users[0] : [];
    }

    /**
     * Create user
     *
     * @var array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {

            Keycloak::createUser($data);

            self::$trialCount = 0;
        } catch (Throwable $exception) {
            info($exception);

            if (self::$trialCount <= 3 && ($exception->getMessage() === __('user.create_failed'))) {
                self::$trialCount += 1;
                \usleep(500);
                return self::create($data);
            }

            $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            return [
                'code' => $errorCode,
                'status' => 'error',
                'message' =>  get_class($exception) === KeycloakException::class
                    ? $exception->getMessage()
                    : __('user.create_failed')
            ];
        }

        $successCode = Response::HTTP_CREATED;

        return [
            'code' => $successCode,
            'status' => 'success',
            'message' => __('user.created')
        ];
    }

    /**
     * Update specific user
     *
     * @var object $user
     * @var array $data
     * @return array
     */
    public function update(string $uuid, array $data): array
    {
        try {
            Keycloak::updateUser($uuid, $data);
            self::$trialCount = 0;
        } catch (Throwable $exception) {
            info($exception);

            if (self::$trialCount <= 3 && ($exception->getMessage() === __('user.update_failed'))) {
                self::$trialCount += 1;
                \usleep(500);
                return self::update($uuid, $data);
            }

            $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            return [
                'code' => $errorCode,
                'status' => 'error',
                'message' => get_class($exception) === KeycloakException::class
                    ? $exception->getMessage()
                    : __('user.update_failed')
            ];
        }

        $successCode = Response::HTTP_OK;

        return [
            'code' => $successCode,
            'status' => 'success',
            'message' => __('user.updated')
        ];
    }

    public function changePassword(string $uuid, array $data): array
    {
        try {
            Keycloak::changePassword($uuid, $data);
            self::$trialCount = 0;
        } catch (Throwable $exception) {
            info($exception);

            if (self::$trialCount <= 3 && ($exception->getMessage() === __('user.change_password_failed'))) {
                self::$trialCount += 1;
                \usleep(500);
                return self::changePassword($uuid, $data);
            }

            $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            return [
                'code' => $errorCode,
                'status' => 'error',
                'message' => get_class($exception) === KeycloakException::class
                    ? $exception->getMessage()
                    : __('user.change_password_failed')
            ];
        }

        $successCode = Response::HTTP_OK;

        return [
            'code' => $successCode,
            'status' => 'success',
            'message' => __('user.password_changed')
        ];
    }
}
