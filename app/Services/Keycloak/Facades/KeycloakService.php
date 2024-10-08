<?php

namespace App\Services\Keycloak\Facades;

use App\Services\Keycloak\Clients\KeycloakServiceClient;
use Illuminate\Support\Facades\Facade;

class KeycloakService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return KeycloakServiceClient::class;
    }
}
