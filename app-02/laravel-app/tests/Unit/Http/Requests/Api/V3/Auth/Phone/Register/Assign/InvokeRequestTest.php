<?php

namespace Tests\Unit\Http\Requests\Api\V3\Auth\Phone\Register\Assign;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Assign\InvokeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_an_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_be_a_unique_with_user_not_disabled()
    {
        User::factory()->create(['email' => 'disabled_user@email.com', 'disabled_at' => Carbon::now()]);
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'disabled_user@email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('auth.account_disabled', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function its_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages(['The email field does not end with a valid tld.']);
    }

    /** @test */
    public function its_email_must_be_unique()
    {
        User::factory()->create(['email' => $email = 'user@email.com']);
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => $email]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $request->assertValidationMessages([Lang::get('validation.unique', ['attribute' => RequestKeys::EMAIL])]);
    }

    /** @test */
    public function it_requires_the_terms_of_services_accepted()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOS_ACCEPTED]);
        $attribute = Str::of(RequestKeys::TOS_ACCEPTED)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::EMAIL        => 'john@doe.com',
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $request->assertValidationPassed();
    }
}
