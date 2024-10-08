<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Auth\User;
use App\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = config('permissions');

        foreach($permissions[UserRole::OPERATION()] as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach(UserRole::values() as $role) {
            Role::firstOrCreate(['name' => $role])
                ->givePermissionTo($permissions[$role] ?? []);
        }

        // Reset cached roles and permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
