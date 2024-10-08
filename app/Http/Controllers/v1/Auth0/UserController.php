<?php

namespace App\Http\Controllers\v1\Auth0;

use App\Abstracts\Controller as BaseController;
use App\Http\Requests\Auth0\UserListRequest;
use App\Http\Requests\Auth0\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Auth\User;
use App\Services\Auth0\Facade\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\ShareInvites;

class UserController extends BaseController
{
    public function index(UserListRequest $userListRequest): AnonymousResourceCollection
    {
        $sortBy = $userListRequest->input('sort') ?? 'id';

        $sortDirection = strtolower($userListRequest->input('direction')) === self::SORT_DESC
            ? self::SORT_DESC
            : self::SORT_ASC;

        $orderBy = User::sortHeaders($sortBy);

        $users = User::whereNotNull('auth0')
            ->filter($userListRequest->input('filter', []))
            ->sortable()
            ->orderBy($orderBy, $sortDirection);

        return UserResource::collection(
            $this->collect($users)
        );
    }

    public function store(UserRequest $request): JsonResponse
    {
        $response = UserService::create($request->only(['user', 'roles', 'note', 'company']));
        return $this->response($response, $response['code']);
    }

    public function update(UserRequest $request, string $userId): JsonResponse
    {
        $response = UserService::update($userId, $request->only(['user', 'roles', 'company']));
        return $this->response($response, $response['code']);
    }

    public function getRoles(): JsonResponse
    {
        $roles = UserService::getRoles();
        return $this->response($roles);
    }

    public function getInvitations(string $userId): JsonResponse
    {
        $invitations = ShareInvites::whereClientId(filter_var($userId, FILTER_UNSAFE_RAW))
            ->with(['parent'])
            ->get();

        return $this->response($invitations);
    }

    public function resendInvitation(string $userId): JsonResponse
    {
        $response = UserService::resendInvitation($userId);
        return $this->response($response, $response['code']);
    }
}
