<?php

namespace App\Services\Auth0;

class Client
{
    protected $management = null;

    public function __construct()
    {
        $this->management = app('auth0')->management();
    }

    public function createUser(array $payload): array
    {
        $request = $this->management->users()->create('Username-Password-Authentication', $payload);

        return $this->decodedContent($request);
    }

    public function updateUser(string $id, $payload): array
    {
        $request = $this->management->users()->update($id, $payload);

        return $this->decodedContent($request);
    }

    public function addUserRoles(string $id, array $roles): bool
    {
        $request = $this->management->users()->addRoles($id, $roles);

        return is_null($this->decodedContent($request));
    }

    public function getUsers(): array
    {
        $request = $this->management->users()->getAll();

        return $this->decodedContent($request);
    }

    public function getUser(string $uuid): array
    {
        $request = $this->management->users()->get($uuid);

        return $this->decodedContent($request);
    }

    public function getRoles(): array
    {
        $request = $this->management->roles()->getAll();

        return $this->decodedContent($request);
    }

    protected function decodedContent(object $request): array
    {
        return json_decode($request->getBody()->getContents(), true);
    }
}
