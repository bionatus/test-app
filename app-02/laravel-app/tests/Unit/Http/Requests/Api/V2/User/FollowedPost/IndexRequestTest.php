<?php

namespace Tests\Unit\Http\Requests\Api\V2\User\FollowedPost;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\User\FollowedPost\IndexRequest;
use DateTimeInterface;
use Exception;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_may_not_get_a_param()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_search_string_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = Str::of(RequestKeys::SEARCH_STRING)->replace('_', ' ');
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_search_string_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING,]);
        $attribute = Str::of(RequestKeys::SEARCH_STRING)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 1000]),
        ]);
    }

    /** @test */
    public function its_created_before_parameter_must_have_a_valid_atom_date_format()
    {
        $requestKey = RequestKeys::CREATED_BEFORE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => '2022/02/08']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'format'    => DateTimeInterface::ATOM,
            ]),
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_should_pass_validation_on_valid_data()
    {

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SEARCH_STRING => 'A filter',
        ]);

        $request->assertValidationPassed();
    }
}
