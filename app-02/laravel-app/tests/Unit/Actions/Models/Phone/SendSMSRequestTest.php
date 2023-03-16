<?php

namespace Tests\Unit\Actions\Models\Phone;

use App;
use App\Actions\Models\Phone\SendSMSRequest;
use App\Constants\RouteNames;
use App\Constants\TwilioErrors;
use App\Events\AuthenticationCode\SmsRequested;
use App\Listeners\AuthenticationCode\SendSmsRequestedNotification;
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

class SendSMSRequestTest extends TestCase
{
    use RefreshDatabase;

    private Phone $phone;
    private string $authenticationCodeType = AuthenticationCode::TYPE_LOGIN;
    private string $message = "SMS's are not possible for the provided phone.";
    private string $routeName = RouteNames::API_V3_AUTH_PHONE_LOGIN_SMS;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phone = Phone::factory()->withUser()->verified()->create();
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_verification_code_via_sms()
    {
        Event::fake(SmsRequested::class);

        $action = new SendSMSRequest($this->phone, AuthenticationCode::TYPE_LOGIN, $this->message);
        $action->execute();

        Event::assertDispatched(SmsRequested::class, function(SmsRequested $event) {
            $this->assertEquals($this->phone->getKey(), $event->authenticationCode()->phone->getKey());

            return true;
        });
    }

    /** @test
     * @throws Exception
     */
    public function it_delete_expired_authentication_codes()
    {
        Event::fake(SmsRequested::class);
        Config::set('communications.sms.code.reset_after', $secondsAgo = 10);

        AuthenticationCode::factory()->count(2)->create();
        $missing = AuthenticationCode::factory()->count(3)->create([
            'created_at' => Carbon::now()->subSeconds($secondsAgo + 1),
        ]);

        $action = new SendSMSRequest($this->phone, AuthenticationCode::TYPE_VERIFICATION, $this->message);
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
        Event::fake(SmsRequested::class);

        $this->assertDatabaseCount(AuthenticationCode::tableName(), 0);

        $action = new SendSMSRequest($this->phone, AuthenticationCode::TYPE_LOGIN, $this->message);
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
        $action = new SendSMSRequest($this->phone, $authenticationCodeType, $this->message);
        $action->execute();
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_validation_exception_on_twilio_invalid_to_phone_number_exception()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');

        $exception = new RestException('', TwilioErrors::INVALID_TO_PHONE_NUMBER, Response::HTTP_BAD_REQUEST);
        $listener  = Mockery::mock(SendSmsRequestedNotification::class)->makePartial();
        $listener->shouldReceive('handle')->withAnyArgs()->once()->andThrow($exception);

        App::bind(SendSmsRequestedNotification::class, fn() => $listener);

        $action = new SendSMSRequest($this->phone, $this->authenticationCodeType, $this->message);
        $action->execute();
    }
}
