<?php

namespace App\Services\Auth0\Facade;

use Illuminate\Support\Facades\Facade;
use App\Services\Auth0\Client\Auth0Client;

class Auth0Service extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Auth0Client::class;
    }
}
