<?php

namespace Tests\Feature\Nova;

use App\User;
use Lang;
use Spatie\Permission\Models\Permission;

class TestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Lang::addNamespace('nova', __DIR__ . '/vendor/laravel/nova/resources/lang');
        Permission::create(['name' => 'access nova']);
        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();
        $user->givePermissionTo('access nova');

        $this->login($user);
    }
}
