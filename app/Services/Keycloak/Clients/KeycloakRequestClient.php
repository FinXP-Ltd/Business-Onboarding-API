<?php

namespace App\Services\Keycloak\Clients;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http as HttpClient;

use App\Enums\UserRole;
use App\Enums\KeycloakActions;
use App\Exceptions\KeycloakException;

class KeycloakRequestClient
{
    const KEYCLOAK_CACHE_PREFIX = 'keycloak_';
    const KEYCLOAK_KEY = self::KEYCLOAK_CACHE_PREFIX . 'access_token';

    const USER_ID = '/users/';

    const ROLE_MAPPING_CLIENTS = '/role-mappings/clients/';

    /** @var string */
    protected $keycloakUrl;

    protected $endpoint;

    public function __construct()
    {
        $this->keycloakUrl = config('keycloak.base_url');
        $this->endpoint = '/auth/admin/realms/' . config('keycloak.realm_name');
    }

    /**
     * Get Access Token from the cache or request a new access token
     *
     * @return string
     */
    protected function getAccessToken(): string
    {
        return Cache::has(self::KEYCLOAK_KEY)
            ? Cache::get(self::KEYCLOAK_KEY)
            : $this->generateAccessToken();
    }

    /**
     * Validate the request to Keycloak
     *
     * @return void
     */
    protected function validateRequest($request, $message = null): void
    {
        if ($request->clientError() || $request->serverError() || !$request->successful()) {
            Cache::forget(self::KEYCLOAK_KEY);

            info($request->json());

            throw new KeycloakException($message ?? ($request->json()['error_description'] ?? 'Server Error' ));
        }
    }

    /**
     * Transform array to get only the necessary keys
     *
     * @param array $items
     * @param array $keys
     *
     * @return array
     */
    protected function transform(array $items, array $keys = []): array
    {
        $items = collect($items);
        if (!empty($keys)) {
            return $items->map(function ($item) use ($keys) {
                return Arr::only($item, $keys);
            })
            ->toArray();
        }

        return $items->toArray();
    }

    /**
     * Generate Access Tokent and save it to Cache
     *
     * @return string
     */
    protected function generateAccessToken(): string
    {
        $credentials = [
            'client_id' => 'admin-cli',
            'grant_type' => 'password',
            'username' => config('keycloak.credentials.username'),
            'password' => config('keycloak.credentials.password')
        ];

        $request = HttpClient::asForm()
            ->post(
                $this->keycloakUrl . '/auth/realms/master/protocol/openid-connect/token',
                $credentials
            );

        $this->validateRequest($request, 'Unable to request an access token to the keycloak server');

        $response = $request->json();

        Cache::put(self::KEYCLOAK_KEY, $response['access_token'], $response['expires_in'] - 1000);

        return $response['access_token'];
    }

    /**
     * Get the list of users from the keycloak
     *
     * @param array $queryParams
     *
     * @return array
     */
    public function getUsers(array $queryParams = []): array
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->get(
                $this->keycloakUrl . $this->endpoint . "/users",
                $queryParams
            );

        $this->validateRequest($request);

        return $request->json();
    }

    public function getUserByClientRole(string $roleName, array $queryParams = []): array
    {
        $clientId = config('keycloak.client_id');

        $request = HttpClient::withToken($this->getAccessToken())
            ->get(
                $this->keycloakUrl . $this->endpoint . "/clients/$clientId/roles/$roleName/users",
                $queryParams
            );

        $this->validateRequest($request);

        return $request->json();
    }

    /**
     * Get the user resource by using uuid
     *
     * @param string $id
     *
     * @return array
     */
    public function getUserById(string $id): array
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->get($this->keycloakUrl . $this->endpoint . self::USER_ID . $id);

        $this->validateRequest($request);

        return $request->json();
    }

    /**
     * Get the total users count
     *
     * @return int
     */
    public function getUsersCount(): int
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->get($this->keycloakUrl . $this->endpoint . '/users/count');

        $this->validateRequest($request);

        return $request->json();
    }

    /**
     * Create user on keycloak
     *
     * @param array $payload
     *
     * @return bool
     */
    public function createUser(array $payload): bool
    {
        $userPayload = [
            'firstName' => $payload['firstName'],
            'lastName' => $payload['lastName'],
            'username' => $payload['email'],
            'email' => $payload['email'],
            'enabled' => $payload['enabled'] ?? false,
            'emailVerified' => $payload['emailVerified'] ?? false,
            'credentials' => $payload['credentials'] ?? [],
            'requiredActions' => $payload['actions'] ?? []
        ];

        $request = HttpClient::withToken($this->getAccessToken())
            ->post(
                $this->keycloakUrl . $this->endpoint . '/users',
                $userPayload
            );

        $this->validateRequest($request);

        return true;
    }

    /**
     * Update user on keycloak
     *
     * @param string $uuid
     * @param array $payload
     *
     * @return bool
     */
    public function updateUser(string $uuid, array $payload): bool
    {
        $userPayload = [
            'firstName' => $payload['firstName'],
            'lastName' => $payload['lastName'],
            'email' => $payload['email'],
            'enabled' => $payload['enabled'] ?? false,
            'emailVerified' => $payload['emailVerified'] ?? false,
        ];

        if (isset($payload['credentials'])) {
            $userPayload['credentials'] = $payload['credentials'];
        }

        if (isset($payload['actions'])) {
            $userPayload['requiredActions'] = $payload['actions'];
        }

        $request = HttpClient::withToken($this->getAccessToken())
            ->put(
                $this->keycloakUrl . $this->endpoint . self::USER_ID . $uuid,
                $userPayload
            );

        $this->validateRequest($request);

        return true;
    }

     /**
     * Change password of user in keycloak
     *
     * @param string $uuid
     * @param array $payload
     *
     * @return bool
     */
    public function changePassword(string $uuid, array $payload): bool
    {
        $userPayload = [
            'type' => 'password',
            'temporary' => false,
            'value' => $payload['password']
        ];

        $request = HttpClient::withToken($this->getAccessToken())
            ->put(
                $this->keycloakUrl . $this->endpoint . self::USER_ID . $uuid . '/reset-password',
                $userPayload
            );

        $this->validateRequest($request);

        return true;
    }

    /**
     * Assign client and role to a user on certain client
     *
     * @param string $userId
     * @param string $clientId
     * @param array $roles
     *
     * @return void
     */
    public function assignResourceToUser(string $userId, string $clientId, array $roles): void
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->post(
                $this->keycloakUrl . $this->endpoint . self::USER_ID . $userId . self::ROLE_MAPPING_CLIENTS . $clientId,
                $roles
            );

        $this->validateRequest($request);
    }

    /**
     * Detach role to the user for a certain client
     *
     * @param string $userId
     * @param string $clientId
     * @param array $roles
     *
     * @return void
     */
    public function removeResourceToUser(string $userId, string $clientId, array $roles): void
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->delete(
                $this->keycloakUrl . $this->endpoint . self::USER_ID . $userId . self::ROLE_MAPPING_CLIENTS . $clientId,
                $roles
            );

        $this->validateRequest($request);
    }

    /**
     * Get the list of clients on current realm
     *
     * @param array $keys
     *
     * @return array
     */
    public function getClients(array $keys = ['id', 'clientId', 'name']): array
    {
        if (Cache::has(self::KEYCLOAK_CACHE_PREFIX . 'clients')) {
            return $this->transform(Cache::get(self::KEYCLOAK_CACHE_PREFIX . 'clients') ?? [], $keys);
        }

        $request = HttpClient::withToken($this->getAccessToken())
            ->get($this->keycloakUrl . $this->endpoint . '/clients');

        $this->validateRequest($request);

        Cache::put(self::KEYCLOAK_CACHE_PREFIX . 'clients', $request->json(), config('cache.default_ttl'));

        return $this->transform($request->json(), $keys);
    }

    /**
     * Get User assigned client roles
     *
     * @param string $userId,
     * @param string $clientId
     * @param array $keys
     *
     * @return array
     */
    public function getUserClientRoles(string $userId, string $clientId, array $keys = ['id', 'name']): array
    {
        $request = HttpClient::withToken($this->getAccessToken())
            ->get(
                $this->keycloakUrl . $this->endpoint . self::USER_ID . $userId . self::ROLE_MAPPING_CLIENTS . $clientId
            );

        $this->validateRequest($request);

        return $this->transform($request->json(), $keys);
    }

    /**
     * Get the list of roles on the client
     *
     * @param string $clientId
     * @param array $keys
     *
     * @return array
     */
    public function getClientRoles(string $clientId, array $keys = ['id', 'name', 'description']): array
    {
        if (Cache::has(self::KEYCLOAK_CACHE_PREFIX . 'client_roles_' . $clientId)) {
            return $this->transform(Cache::get(self::KEYCLOAK_CACHE_PREFIX . 'client_roles_' . $clientId) ?? [], $keys);
        }

        $request = HttpClient::withToken($this->getAccessToken())
            ->get($this->keycloakUrl . $this->endpoint . '/clients/' . $clientId . '/roles');

        $this->validateRequest($request);

        Cache::put(self::KEYCLOAK_CACHE_PREFIX . 'client_roles_' . $clientId, $request->json(), config('cache.default_ttl'));

        return $this->transform($request->json(), $keys);
    }

    public function mapResources(string $client, string $role = 'customers')
    {
        $clientRoles = self::getClientRoles($client, ['id', 'name']);

        return collect($clientRoles)
            ->filter(function ($client) use ($role) {
                return $client['name'] === config('keycloak.client_scopes.api')
                    || $client['name'] === $role;
            })
            ->toArray();
    }
}
