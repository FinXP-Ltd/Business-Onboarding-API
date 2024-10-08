<?php

namespace App\Services\Auth0\Client;

use App\Exceptions\Auth0Exception;
use Illuminate\Http\Response as HttpResponse;

class Auth0Client
{
    protected $management = null;

    public function __construct()
    {
        $this->management = app('auth0')->management();
    }

    public function createUser(array $payload): array
    {
        $request = $this->management->users()->create('Username-Password-Authentication', $payload);

        $this->validateRequest($request, HttpResponse::HTTP_CREATED);

        return $this->decodedContent($request);
    }

    public function updateUser(string $id, $payload): array
    {
        $request = $this->management->users()->update($id, $payload);

        $this->validateRequest($request);

        return $this->decodedContent($request);
    }

    public function deleteUser(string $id): bool
    {
        $request = $this->management->users()->delete($id);

        $this->validateRequest($request, HttpResponse::HTTP_NO_CONTENT);

        return is_null($this->decodedContent($request));
    }

    public function addUserRoles(string $id, array $roles): bool
    {
        $this->management->users()->removeRoles($id, $roles);
        $request = $this->management->users()->addRoles($id, $roles);

        $this->validateRequest($request, HttpResponse::HTTP_NO_CONTENT);

        return is_null($this->decodedContent($request));
    }

    public function getUser(string $uuid): array
    {
        $request = $this->management->users()->get($uuid);

        $this->validateRequest($request);

        $user = $this->decodedContent($request);

        if ($user) {
            $user['roles'] = $this->getRole($uuid);
        }

        return $user;
    }

    public function getRole(string $uuid): array
    {
        $request = $this->management->users()->getRoles($uuid);

        $this->validateRequest($request);

        return $this->decodedContent($request);
    }

    public function getRoles(): array
    {
        $request = $this->management->roles()->getAll();

        $this->validateRequest($request);

        return $this->decodedContent($request);
    }

    public function generateResetPasswordLink(array $data): array
    {
        $request = $this->management->tickets()->createPasswordChange($data);

        $this->validateRequest($request, HttpResponse::HTTP_CREATED);

        return $this->decodedContent($request);
    }

    protected function decodedContent(object $request): mixed
    {
        if ($request->getBody() === null) {
            return null;
        }

        return json_decode($request->getBody()->getContents(), true);
    }

    /**
     * Validate the request to Keycloak
     *
     * @return void
     */
    protected function validateRequest($response, $expectedStatusCode = HttpResponse::HTTP_OK): void
    {
        if ($response->getStatusCode() !== $expectedStatusCode) {
            $content = $this->decodedContent($response) ?? [];
            $statusCode = $content['statusCode'] ?? HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

            info($response);

            throw new Auth0Exception($content['message'] ?? 'Server Error', $statusCode);
        }
    }
}
