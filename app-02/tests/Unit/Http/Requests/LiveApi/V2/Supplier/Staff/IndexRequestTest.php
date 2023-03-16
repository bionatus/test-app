<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Supplier\Staff;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V2\Supplier\Staff\IndexRequest;
use App\Models\Staff;
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
    public function its_type_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test
     * @dataProvider StaffTypeProvider
     */
    public function it_should_be_type_a_valid_staff_type($staffType)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TYPE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    public function StaffTypeProvider(): array
    {
        return [
            [Staff::TYPE_MANAGER],
            [Staff::TYPE_COUNTER],
            [Staff::TYPE_ACCOUNTANT],
            [Staff::TYPE_OWNER],
            [Staff::TYPE_CONTACT],
            [null],
        ];
    }

    /**
     * @test
     * @dataProvider StaffTypeProvider
     */
    public function it_pass_on_valid_data(string|null $type)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
        ]);

        $request->assertValidationPassed();
    }
}
