<?php

namespace Tests\Feature\Api\V3\Auth\CreatePassword;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\CreatePasswordController;
use App\Http\Requests\Api\V3\Auth\CreatePassword\StoreRequest;
use App\Models\User;
use App\Notifications\CreatePasswordNotification;
use Config;
use DB;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;
use JMac\Testing\Traits\AdditionalAssertions;
use Lang;
use Notification;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see CreatePasswordController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_AUTH_CREATE_PASSWORD;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function create_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => $user->email]);

        $response->assertStatus(Response::HTTP_CREATED);

        Notification::assertSentTo($user, CreatePasswordNotification::class,
            function(CreatePasswordNotification $notification) use ($user) {
                $broker = Password::broker('latam');
                $this->assertTrue($broker->tokenExists($user, $notification->token));

                return true;
            });
    }

    /** @test */
    public function it_returns_a_validation_exception_on_invalid_user()
    {
        Notification::fake();

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => 'invalid@email.com']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => Lang::get('passwords.user')]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_returns_a_validation_exception_on_throttled()
    {
        Notification::fake();

        $user = User::factory()->create();

        $table = Config::get('auth.passwords.latam.table');
        DB::table($table)->insert([
            'email'      => $user->email,
            'token'      => 'token',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => $user->email]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => Lang::get('passwords.throttled')]);

        Notification::assertNothingSent();
    }
}
