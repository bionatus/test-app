<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V2\Order\IndexRequest;
use App\Models\Order;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function its_type_param_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_type_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test
     * @dataProvider typeProvider
     */
    public function it_pass_on_valid_data($type)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
        ]);

        $request->assertValidationPassed();
    }

    public function typeProvider(): array
    {
        return [
            [Order::TYPE_ORDER_LIST_AVAILABILITY],
            [Order::TYPE_ORDER_LIST_APPROVED],
        ];
    }
}
