<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Auth\User;
use App\Models\Role;
use App\Models\Permission;

class RoleAndPermissionTest extends TestCase
{

    public function testItShouldCreateUserWithPermissionAndRoleClient()
    {
        $role = Role::findOrCreate('client');
        $permission = Permission::findOrCreate('can view');
        $user = User::factory()->create();
        $user->assignRole('client');


        $this->assertEquals($user->hasRole('client'), 1);
        $this->assertEquals($user->hasPermissionTo('can view'), 1);
    }
}
