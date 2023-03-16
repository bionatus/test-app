<?php

namespace Tests\Feature\Api\V3\Auth\Phone\Register\Call;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\CallRequested;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\CallController;
use App\Http\Resources\Api\V3\Auth\CodeRequestedResource;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Call\BaseResource;
use App\Listeners\AuthenticationCode\StartPhoneAuthenticationCall;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\User;
use Config;
use Event;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use Twilio\Exceptions\RestException;
use URL;

/** @see CallController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_AUTH_PHONE_REGISTER_CALL;

    /** @test
     * @throws Exception
     */
    public function it_sends_a_verification_code_via_phone_call()
    {
        Event::fake(CallRequested::class);

        $phone = Phone::factory()->unverified()->create();

        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = $response->json('data');
        $this->assertEquals(CodeRequestedResource::TYPE_SIGN_UP, $data['type']);

        Event::assertDispatched(CallRequested::class, function(CallRequested $event) use ($phone) {
            $this->assertEquals($phone->getKey(), $event->phone()->getKey());

            return true;
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_validation_exception_on_twilio_failing_validation()
    {
        $exception = new RestException('', TwilioErrors::FROM_PHONE_NUMBER_NOT_VERIFIED, Response::HTTP_BAD_REQUEST);
        $listener  = Mockery::mock(StartPhoneAuthenticationCall::class);
        $listener->makePartial();
        $listener->shouldReceive('handle')->withAnyArgs()->once()->andThrow($exception);

        App::bind(StartPhoneAuthenticationCall::class, fn() => $listener);

        $phone = Phone::factory()->unverified()->create();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([RequestKeys::PHONE => 'Calls are not possible for the provided phone.']);
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

        $phone = Phone::factory()->unverified()->create();

        $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $missing->each(function(AuthenticationCode $authenticationCode) {
            $this->assertDatabaseMissing(AuthenticationCode::tableName(), [
                'id' => $authenticationCode->getKey(),
            ]);
        });
    }

    /**
     * @test
     */
    public function it_should_return_validation_exception_if_user_is_disabled()
    {
        $user  = User::factory()->create(['disabled_at' => Carbon::now()]);
        $phone = Phone::factory()->usingUser($user)->verified()->create();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            RequestKeys::PHONE => 'The account has been disabled.',
        ]);
    }
}
