<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Order\IndexRequest;
use App\Models\Status;
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
    public function its_status_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = Str::of(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_status_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_values(string $statusOrder)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS => $statusOrder]);

        $request->assertValidationPassed();
    }

    public function dataProvider(): array
    {
        return [
            [Status::STATUS_NAME_PENDING],
            [Status::STATUS_NAME_PENDING_APPROVAL],
            [Status::STATUS_NAME_APPROVED],
            [Status::STATUS_NAME_COMPLETED],
            [Status::STATUS_NAME_CANCELED],
        ];
    }
}
