<?php

namespace Tests\Feature\Api\Nova;

use App\User;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use JMac\Testing\Traits\AdditionalAssertions;
use Spatie\Permission\Models\Permission;

class TestCase extends \Tests\TestCase
{
    use AdditionalAssertions;

    protected function login(?UserContract $user = null): UserContract
    {
        Permission::create(['name' => 'access nova']);

        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();

        $user->givePermissionTo('access nova');
        $this->actingAs($user);

        return $user;
    }
}
