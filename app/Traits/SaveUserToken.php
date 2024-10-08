<?php

namespace App\Traits;

use App\Enums\UserRole;
use DB;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;
use Exception;

trait SaveUserToken
{
    public function saveUsers()
    {
        $authId = auth()->id();
        $user = User::whereAuth0(auth()->id())->first();
        $profile = app('auth0')->management()->users()->get(auth()->id());
        $profile = app('auth0')->json($profile);

        $fullName = $profile['name'] ?? 'Unknown';
        $name = explode(',', $fullName);

        if (!$user) {
            DB::beginTransaction();

            try {
                $user = User::create([
                    'name' => Auth::user()->token->name,
                    'email' => Auth::user()->token->preferred_username,
                    'auth0' => $authId,
                    'program_id' => $this->getUserProgramId(),
                    'first_name' => isset($name[1]) ? $name[1] : '',
                    'last_name' => isset($name[0]) ? $name[0] : ''
                ]);

                DB::commit();
            } catch (Exception $e) {
                info($e);
                DB::rollBack();
            }
        }

        return $user->id;
    }

    public function getUserProgramId()
    {
        $betterPayment =  config('kycp.programme.better_payment');
        $customer = config('kycp.programme.customers');

        return (hasAuth0Role(UserRole::BETTER_PAYMENT()))
            ? $betterPayment
            : $customer;
    }
}
