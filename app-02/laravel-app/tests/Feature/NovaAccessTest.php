<?php

namespace Tests\Feature;

use App\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\Exceptions\AuthenticationException;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NovaAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test * */
    public function app_is_named_correctly()
    {
        $this->assertEquals('Bluon Energy', Config::get('nova.name'));
    }

    /** @test * */
    public function app_is_at_the_correct_url()
    {
        $this->assertEquals('/nova', Config::get('nova.path'));
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(AuthenticationException::class);

        $this->get(Config::get('nova.path'));
    }

    /** @test */
    public function an_authenticated_user_can_not_proceed()
    {
        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();

        $this->login($user);

        $response = $this->get(Config::get('nova.path'));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test * */
    public function an_authenticated_user_with_right_permission_can_access()
    {
        $user = new User([
            'email'    => 'example@test.com',
            'password' => 'password',
        ]);
        $user->saveQuietly();

        Permission::create(['name' => 'access nova']);
        $user->givePermissionTo('access nova');

        $this->login($user);

        $response = $this->get(Config::get('nova.path'));

        $response->assertStatus(Response::HTTP_OK);
    }
}
