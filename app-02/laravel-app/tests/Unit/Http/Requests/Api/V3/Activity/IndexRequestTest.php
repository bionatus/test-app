<?php

namespace Tests\Unit\Http\Requests\Api\V3\Activity;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Activity\IndexRequest;
use App\Models\Activity;
use Lang;
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
    public function log_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOG_NAME => 2]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOG_NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::LOG_NAME)]),
        ]);
    }

    /** @test */
    public function log_name_must_be_a_valid_option()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOG_NAME => 'other_type']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOG_NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::LOG_NAME)]),
        ]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_values($type)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOG_NAME => $type]);

        $request->assertValidationPassed();
    }

    public function dataProvider()
    {
        return [
            [Activity::TYPE_FORUM],
            [Activity::TYPE_ORDER],
            [Activity::TYPE_PROFILE],
        ];
    }
}
