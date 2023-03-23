<?php

namespace Tests\Unit\Actions\Models\Phone;

use App;
use App\Actions\Models\Phone\SendCallRequest;
use App\Constants\RouteNames;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\CallRequested;
use App\Listeners\AuthenticationCode\StartPhoneAuthenticationCall;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Config;
use Event;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use Twilio\Exceptions\RestException;

class SendCallRequestTest extends TestCase
{
    use RefreshDatabase;

    private Phone  $phone;
    private string $authenticationCodeType = AuthenticationCode::TYPE_LOGIN;
    private string $routeName              = RouteNames::API_V3_AUTH_PHONE_LOGIN_CALL;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phone = Phone::factory()->withUser()->verified()->create();
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_verification_code_via_phone_call()
    {
        Event::fake(CallRequested::class);

        $action = new SendCallRequest($this->phone, $this->authenticationCodeType);
        $action->execute();

        Event::assertDispatched(CallRequested::class, function(CallRequested $event) {
            $this->assertEquals($this->phone->getKey(), $event->phone()->getKey());

            return true;
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_delete_expired_authentication_codes()
    {
        Event::fake(CallRequested::class);
        Config::set('communications.sms.code.reset_after', $secondsAgo = 10);

        AuthenticationCode::factory()->count(2)->create();
        $missing = AuthenticationCode::factory()->count(3)->create([
            'created_at' => Carbon::now()->subSeconds($secondsAgo + 1),
        ]);

        $action = new SendCallRequest($this->phone, $this->authenticationCodeType);
        $action->execute();

        $missing->each(function(AuthenticationCode $authenticationCode) {
            $this->assertDatabaseMissing(AuthenticationCode::tableName(), [
                'id' => $authenticationCode->getKey(),
            ]);
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_add_a_new_authentication_code()
    {
        Event::fake(CallRequested::class);

        $this->assertDatabaseCount(AuthenticationCode::tableName(), 0);

        $action = new SendCallRequest($this->phone, $this->authenticationCodeType);
        $action->execute();

        $this->assertDatabaseCount(AuthenticationCode::tableName(), 1);
    }

    /** @test
     * @throws Exception
     */
    public function it_throw_an_exception_when_authentication_code_type_is_wrong()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid authentication code type');

        $authenticationCodeType = 'authentication_code_type';
        $action                 = new SendCallRequest($this->phone, $authenticationCodeType);
        $action->execute();
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_validation_exception_on_twilio_from_phone_number_not_verified_exception()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        $exception = new RestException('', TwilioErrors::FROM_PHONE_NUMBER_NOT_VERIFIED, Response::HTTP_BAD_REQUEST);
        $listener  = Mockery::mock(StartPhoneAuthenticationCall::class)->makePartial();
        $listener->shouldReceive('handle')->withAnyArgs()->once()->andThrow($exception);

        App::bind(StartPhoneAuthenticationCall::class, fn() => $listener);

        $action = new SendCallRequest($this->phone, $this->authenticationCodeType);
        $action->execute();
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_validation_exception_on_twilio_call_geo_permissions_not_enabled_exception()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        $exception = new RestException('', TwilioErrors::CALL_GEO_PERMISSIONS_NOT_ENABLED, Response::HTTP_BAD_REQUEST);
        $listener  = Mockery::mock(StartPhoneAuthenticationCall::class)->makePartial();
        $listener->shouldReceive('handle')->withAnyArgs()->once()->andThrow($exception);

        App::bind(StartPhoneAuthenticationCall::class, fn() => $listener);

        $action = new SendCallRequest($this->phone, $this->authenticationCodeType);
        $action->execute();
    }
}
