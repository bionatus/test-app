<?php

namespace Tests\Feature\LiveApi\V1\Auth\Email\ForgotPassword;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Auth\Email\ForgotPasswordController;
use App\Http\Requests\LiveApi\V1\Auth\Email\ForgotPassword\StoreRequest;
use App\Models\Staff;
use App\Notifications\LiveApi\V1\ResetPasswordNotification;
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

/** @see ForgotPasswordController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_AUTH_EMAIL_FORGOT_PASSWORD_STORE;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function password_reset_link_can_be_requested()
    {
        Notification::fake();

        $staff = Staff::factory()->withEmail()->createQuietly();

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => $staff->email]);

        $response->assertStatus(Response::HTTP_CREATED);

        Notification::assertSentTo($staff, ResetPasswordNotification::class,
            function(ResetPasswordNotification $notification) use ($staff) {
                $broker = Password::broker('live');
                $this->assertTrue($broker->tokenExists($staff, $notification->token));

                return true;
            });
    }

    /** @test */
    public function it_returns_created_and_do_not_send_an_email_on_invalid_user()
    {
        Notification::fake();

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => 'invalid@email.com']);

        $response->assertStatus(Response::HTTP_CREATED);

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_returns_a_validation_exception_on_throttled()
    {
        Notification::fake();

        $staff = Staff::factory()->withEmail()->createQuietly();

        $table = Config::get('auth.passwords.live.table');
        DB::table($table)->insert([
            'email'      => $staff->email,
            'token'      => 'token',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => $staff->email]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::EMAIL => Lang::get('passwords.throttled')]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_returns_created_and_do_not_send_an_email_to_not_owner_staff()
    {
        Notification::fake();

        $staff = Staff::factory()->manager()->withEmail()->createQuietly();

        $response = $this->post(URL::route($this->routeName), [RequestKeys::EMAIL => $staff->email]);

        $response->assertStatus(Response::HTTP_CREATED);

        Notification::assertNothingSent();
    }
}
