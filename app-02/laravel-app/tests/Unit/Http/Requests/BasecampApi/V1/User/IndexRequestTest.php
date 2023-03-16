<?php

namespace Tests\Unit\Http\Requests\BasecampApi\V1\User;

use App\Constants\RequestKeys;
use App\Http\Requests\BasecampApi\V1\User\IndexRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function its_users_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::USERS => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USERS]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::USERS])]);
    }

    /** @test */
    public function its_users_parameter_must_be_an_string_representing_an_array_with_max_100_items()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::USERS => implode(',', collect()->range(1, 101)->all())]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USERS]);
        $request->assertValidationMessages([
            Lang::get('The :attribute may not have more than :max items.',
                ['attribute' => RequestKeys::USERS, 'max' => 100]),
        ]);
    }

    /** @test */
    public function its_users_parameter_must_contain_only_integers()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::USERS => implode(',', [1, 2, 'a'])]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USERS]);
        $request->assertValidationMessages([
            Lang::get('The values in :attribute must be integers.', ['attribute' => RequestKeys::USERS]),
        ]);
    }

    /** @test */
    public function it_should_pass_with_existing_users_in_users_parameter()
    {
        $userOne        = User::factory()->create(['first_name' => 'Name']);
        $userTwo        = User::factory()->create(['last_name' => 'Lastname']);
        $usersParameter = implode(',', [$userOne->getKey(), $userTwo->getKey()]);

        $request = $this->formRequest($this->requestClass, [RequestKeys::USERS => $usersParameter]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_should_pass_with_existing_and_non_existing_users_in_users_parameter()
    {
        $user           = User::factory()->create();
        $usersParameter = implode(',', [$user->getKey(), 2, 3, 4, 5]);

        $request = $this->formRequest($this->requestClass, [RequestKeys::USERS => $usersParameter]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_search_string_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_search_string_parameter_should_be_at_least_3_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(2)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 3]),
        ]);
    }

    /** @test */
    public function its_users_parameter_and_search_string_parameter_cannot_be_sent_together()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::USERS => '1,2', RequestKeys::SEARCH_STRING => 'valid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USERS, RequestKeys::SEARCH_STRING]);
        $searchStringAttribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);

        $request->assertValidationMessages([
            Lang::get('validation.prohibited_unless',
                ['attribute' => RequestKeys::USERS, 'other' => $searchStringAttribute, 'values' => 'null']),
            Lang::get('validation.prohibited_unless',
                ['attribute' => $searchStringAttribute, 'other' => RequestKeys::USERS, 'values' => 'null']),
        ]);
    }

    /** @test */
    public function its_users_parameter_or_search_string_parameter_should_be_present()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USERS, RequestKeys::SEARCH_STRING]);
        $searchStringAttribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);

        $request->assertValidationMessages([
            Lang::get('validation.required_without',
                ['attribute' => RequestKeys::USERS, 'values' => $searchStringAttribute]),
            Lang::get('validation.required_without',
                ['attribute' => $searchStringAttribute, 'values' => RequestKeys::USERS]),
        ]);
    }
}
