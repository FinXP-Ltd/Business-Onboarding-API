<?php

namespace App\Services\Keycloak\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Keycloak\Clients\KeycloakRequestClient;

class KeycloakRequest extends Facade
{
    const KEYCLOAK_CACHE_PREFIX = KeycloakRequestClient::KEYCLOAK_CACHE_PREFIX;
    const KEYCLOAK_KEY = KeycloakRequestClient::KEYCLOAK_KEY;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return KeycloakRequestClient::class;
    }
}
