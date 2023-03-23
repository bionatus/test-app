<?php

namespace Tests\Feature\Api\V3\Account\Phone\Sms;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\SmsRequested;
use App\Http\Controllers\Api\V3\Account\Phone\SmsController;
use App\Http\Requests\Api\V3\Account\Phone\Sms\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Phone\Sms\BaseResource;
use App\Listeners\AuthenticationCode\SendSmsRequestedNotification;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\User;
use Config;
use Event;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use Twilio\Exceptions\RestException;
use URL;

/** @see SmsController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_PHONE_SMS;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_an_authentication_verification_code()
    {
        Event::fake(SmsRequested::class);

        $phone = Phone::factory()->make();

        $this->login();
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        Event::assertDispatched(SmsRequested::class, function(SmsRequested $event) use ($phone) {
            $this->assertEquals($phone->fullNumber(), $event->authenticationCode()->phone->fullNumber());

            return true;
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_validation_exception_on_twilio_failing_validation()
    {
        $exception = new RestException('', TwilioErrors::INVALID_TO_PHONE_NUMBER, Response::HTTP_BAD_REQUEST);
        $listener  = Mockery::mock(SendSmsRequestedNotification::class);
        $listener->makePartial();
        $listener->shouldReceive('handle')->withAnyArgs()->once()->andThrow($exception);

        App::bind(SendSmsRequestedNotification::class, fn() => $listener);

        $phone = Phone::factory()->make();

        $this->login();
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            RequestKeys::PHONE => Lang::get('validation.in', ['attribute' => RequestKeys::PHONE]),
        ]);
    }

    /** @test
     * @throws Exception
     */
    public function it_delete_expired_authentication_codes()
    {
        Event::fake();

        Config::set('communications.sms.code.reset_after', $secondsAgo = 10);

        AuthenticationCode::factory()->count(2)->create();
        $missing = AuthenticationCode::factory()->count(3)->create([
            'created_at' => Carbon::now()->subSeconds($secondsAgo + 1),
        ]);

        $user = User::factory()->create();

        $this->login($user);

        $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => 1,
            RequestKeys::PHONE        => 555222810,
        ]);

        $missing->each(function(AuthenticationCode $authenticationCode) {
            $this->assertDatabaseMissing(AuthenticationCode::tableName(), [
                'id' => $authenticationCode->getKey(),
            ]);
        });
    }
}
