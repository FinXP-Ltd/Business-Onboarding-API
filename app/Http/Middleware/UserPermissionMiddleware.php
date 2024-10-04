<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

use App\Models\Auth\User;

class UserPermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        if (app('auth')->guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (hasAuth0Role(UserRole::OPERATION())) {
            return $next($request);
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (app('auth')->guard($guard)->user()->can($permission)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forPermissions($permissions);
    }
}
