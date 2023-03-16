<?php

namespace Tests\Unit\Http\Requests\Api\V2\Support\Ticket\AgentRate;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Support\Ticket\AgentRate\StoreRequest;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see AgentRateController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_rating()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::RATING]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::RATING])]);
    }

    /** @test */
    public function its_rating_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::RATING => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::RATING]);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => RequestKeys::RATING])]);
    }

    /** @test */
    public function its_rating_must_be_greater_or_equal_to_one()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::RATING => 0]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::RATING]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => RequestKeys::RATING,
                'min'       => 1,
            ]),
        ]);
    }

    /** @test */
    public function its_rating_must_be_lower_or_equal_to_five()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::RATING => 6]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::RATING]);
        $request->assertValidationMessages([
            Lang::get('validation.max.numeric', [
                'attribute' => RequestKeys::RATING,
                'max'       => 5,
            ]),
        ]);
    }

    /** @test */
    public function its_comment_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMMENT => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMMENT]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::COMMENT])]);
    }

    /** @test */
    public function its_comment_should_have_at_most_400_characters()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::RATING  => 1,
            RequestKeys::COMMENT => Str::random(401),
        ]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMMENT]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::COMMENT,
                'max'       => 400,
            ]),
        ]);
    }
}
