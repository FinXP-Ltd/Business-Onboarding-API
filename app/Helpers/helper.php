<?php

use App\Enums\ClientType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('hasAuth0Role')) {
    function hasAuth0Role(string $role): bool
    {
        $roles = getAuth0Roles();

        return !empty($roles) && in_array($role, $roles);
    }
}

if (!function_exists('getAuth0Roles')) {
    function getAuth0Roles(): array
    {
        $namespace = 'https://finxp.com/roles';

        return auth()->user()?->$namespace ?? [];
    }
}

if (!function_exists('hasAuth0AnyRole')) {
    function hasAuth0AnyRole(array | string $role): bool
    {
        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        foreach ($roles as $role) {
            if (hasAuth0Role($role)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getClientType')) {
    function getClientType(): mixed
    {
        $azp = auth()->user()?->azp;

        $clientType = null;

        switch ($azp) {
            case config('client_id.zbx'):
                $clientType = ClientType::ZBX();
                break;

            case config('client_id.bp'):
                $clientType = ClientType::BP();
                break;

            case config('client_id.app'):
                $clientType = ClientType::APP();
                break;

            default:
                break;
        }

        return $clientType;
    }
}

if (!function_exists('getAuthorizationType')) {
    function getAuthorizationType(): mixed
    {
        return auth()->user()?->gty;
    }
}

if (!function_exists('generateFilename')) {
    /**
     * Generate filename
     *
     * @param string $disk
     * @param string $path
     * @param UploadedFile $file
     * @param integer $count
     * @return string $filename
     */
    function generateFilename(string $disk, string $path, UploadedFile $file, int $count = 0)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . ($count == 0 ? "." . $extension :  "({$count})." . $extension);
        $filePath = Str::of($path)->finish(DIRECTORY_SEPARATOR)->finish($filename)->toString();
        if (Storage::disk($disk)->exists($filePath)) {
            $count++;
            return generateFilename($disk, $path, $file, $count);
        }

        return $filename;
    }
}

