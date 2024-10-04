<?php

namespace App\Services\Auth0;

use Illuminate\Support\Facades\Facade;

class Auth0Service extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
