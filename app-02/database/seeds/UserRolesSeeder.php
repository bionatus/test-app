<?php

namespace Database\Seeders;

use App\Constants\Environments;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRolesSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    public function run()
    {
        $administrator_role = Role::create(['name' => 'administrator']);
        Role::create(['name' => 'contractor']);

        $access_nova_permission = Permission::create(['name' => 'access nova']);

        $administrator_role->givePermissionTo($access_nova_permission);
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_TESTING;
    }
}
