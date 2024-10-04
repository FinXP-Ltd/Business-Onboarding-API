<?php

return [
    'realm_public_key' => env('KEYCLOAK_REALM_PUBLIC_KEY', null),

    'realm_name' => env('KEYCLOAK_REALM', null),

    'load_user_from_database' => env('KEYCLOAK_LOAD_USER_FROM_DATABASE', false),

    'user_provider_custom_retrieve_method' => null,

    'user_provider_credential' => env('KEYCLOAK_USER_PROVIDER_CREDENTIAL', 'username'),

    'token_principal_attribute' => env('KEYCLOAK_TOKEN_PRINCIPAL_ATTRIBUTE', 'preferred_username'),

    'append_decoded_token' => env('KEYCLOAK_APPEND_DECODED_TOKEN', true),

    'allowed_resources' => env('KEYCLOAK_ALLOWED_RESOURCES', null),

    'ignore_resources_validation' => env('KEYCLOAK_IGNORE_RESOURCES_VALIDATION', true),

    'leeway' => env('KEYCLOAK_LEEWAY', 0),

    'input_key' => env('KEYCLOAK_TOKEN_INPUT_KEY', null),

    'bo_key' => env('BUSINESS_ONBOARDING_CLIENT', null),

    'base_url' => env('KEYCLOAK_API_URL', null),

    'auth_origin' => env('AUTH_ORIGIN', null),

    'auth_cookie_expiration' => env('AUTH_COOKIE_EXPIRATION', 15),

    /**
     * Client
     */
    'client_id' => env('KEYCLOAK_CLIENT_ID', null),

    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', null),

    'client_name' => env('KEYCLOAK_CLIENT_NAME', null),

    'grant_type' => env('KEYCLOAK_GRANT_TYPE', null),

    'scope' => env('KEYCLOAK_CLIENT_SCOPE', 'openid'),

    /**
     * User credentials to access the keycloak via REST API
     *
     */
    'credentials' => [
        'username' => env('KEYCLOAK_CREDENTIALS_USERNAME', null),

        'password' => env('KEYCLOAK_CREDENTIALS_PASSWORD', null)
    ],
];
