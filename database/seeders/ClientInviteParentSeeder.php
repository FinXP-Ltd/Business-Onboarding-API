<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\UserRole;
use App\Models\Auth\User;
use App\Models\ShareInvites;
use Illuminate\Database\Seeder;

class ClientInviteParentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $clientInvites = ShareInvites::get();

        $clientInvites->each(function(ShareInvites $clientInvite) {

            $user = User::find($clientInvite->client_id);

            if ($user && $user->hasRole(UserRole::INVITED_CLIENT()) && !$clientInvite->head_parent_id) {
                $clientInvite->update(['head_parent_id' => $clientInvite->parent_id]);
            }
        });
    }
}
