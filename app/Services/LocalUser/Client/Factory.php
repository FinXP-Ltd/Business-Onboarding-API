<?php

namespace App\Services\LocalUser\Client;

use App\Enums\ClientType;
use App\Enums\UserRole;
use App\Models\Auth\User;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Factory
{
    public function createOrFetchLocalUser(): User
    {
        $authId = auth()->id();

        $user = User::where('auth0', $authId)->first();

        $type = getAuthorizationType();

        $clientType = getClientType();

        if ($type !== 'client-credentials') {
            $profile = app('auth0')->management()->users()->get(auth()->id());
            $profile = app('auth0')->json($profile);

            $name = $profile['name'] ?? 'Unknown';
            $email = $profile['email'] ?? 'Unknown';

            $name = explode(',', $name);
        } else {
            $clientType = getClientType();

            $name = [$clientType, $clientType];

            $email = "$clientType@finxp.com";
        }

        if (!$user) {
            DB::beginTransaction();

            try {
                $user = User::create([
                    'email' => $email,
                    'auth0' => $authId,
                    'program_id' => $this->getUserProgramId(),
                    // We can't guarantee that a name is provided so for now let's just use the username.
                    //
                    // PHP will throw 'undefined property' if we try to use `name` when the `name`
                    // token claim doesn't exist.
                    'first_name' => isset($name[1]) ? $name[1] : '',
                    'last_name' => isset($name[0]) ? $name[0] : ''
                ]);

                if ($type === 'client-credentials') {
                    $user->syncRoles([UserRole::CLIENT()]);
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();

                info($e);

            }
        }

        return $user;
    }

    public function getUserProgramId()
    {
        $clientType = getClientType();

        $programId = config('kycp.program_id.customers');

        switch ($clientType) {
            case ClientType::BP():
                $programId = config('kycp.program_id.better_payment');
                break;

            case ClientType::ZBX():
                $programId = config('kycp.program_id.zbx');
                break;

            default:
                break;
        }

        return $programId;
    }
}
