<?php

namespace Tests\Feature\LiveApi\V1\Auth\Email\ResetPassword;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Auth\Email\ResetPasswordController;
use App\Http\Requests\LiveApi\V1\Auth\Email\ResetPassword\StoreRequest;
use App\Models\Staff;
use Config;
use DB;
use Exception;
use Hash;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see ResetPasswordController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_AUTH_EMAIL_RESET_PASSWORD_STORE;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_sets_a_new_password()
    {
        $accountantStaff = Staff::factory()->accountant()->withEmail()->createQuietly();
        $ownerStaff      = Staff::factory()->owner()->createQuietly(['email' => $accountantStaff->email]);
        $table           = Config::get('auth.passwords.live.table');
        $token           = 'token';

        DB::table($table)->insert([
            'email'      => $ownerStaff->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::TOKEN                 => $token,
            RequestKeys::EMAIL                 => $ownerStaff->email,
            RequestKeys::PASSWORD              => $password = 'new-password',
            RequestKeys::PASSWORD_CONFIRMATION => $password,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(Lang::get(PasswordBroker::PASSWORD_RESET), $response->json('message'));

        $this->assertDatabaseCount($table, 0);

        $this->assertTrue(Hash::check($password, $ownerStaff->fresh()->password));
    }

    /** @test */
    public function it_returns_a_validation_exception_on_invalid_user()
    {
        $table = Config::get('auth.passwords.live.table');
        $token = 'token';

        DB::table($table)->insert([
            'email'      => $email = 'email@email.com',
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::TOKEN                 => $token,
            RequestKeys::EMAIL                 => $email,
            RequestKeys::PASSWORD              => $password = 'password',
            RequestKeys::PASSWORD_CONFIRMATION => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => 'The password reset token and email combination is invalid.']);
    }

    /** @test */
    public function it_returns_a_validation_exception_on_invalid_token()
    {
        $staff = Staff::factory()->withEmail()->createQuietly();
        $table = Config::get('auth.passwords.live.table');
        $token = 'token';

        DB::table($table)->insert([
            'email'      => $staff->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::TOKEN                 => 'invalid-token',
            RequestKeys::EMAIL                 => $staff->email,
            RequestKeys::PASSWORD              => $password = 'password',
            RequestKeys::PASSWORD_CONFIRMATION => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => 'The password reset token and email combination is invalid.']);
    }

    /** @test */
    public function it_returns_a_validation_exception_on_expired_token()
    {
        $staff = Staff::factory()->withEmail()->createQuietly();
        $table = Config::get('auth.passwords.live.table');
        $ttl   = Config::get('auth.passwords.live.expire');
        $token = 'token';

        DB::table($table)->insert([
            'email'      => $staff->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now()->subminutes($ttl + 1),
        ]);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::TOKEN                 => 'invalid-token',
            RequestKeys::EMAIL                 => $staff->email,
            RequestKeys::PASSWORD              => $password = 'password',
            RequestKeys::PASSWORD_CONFIRMATION => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => 'The password reset token and email combination is invalid.']);
    }
}
