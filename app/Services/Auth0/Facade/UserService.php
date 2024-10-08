<?php

namespace App\Services\Auth0\Facade;

use App\Services\Auth0\Client\UserClient;
use Illuminate\Support\Facades\Facade;

class UserService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UserClient::class;
    }
}
