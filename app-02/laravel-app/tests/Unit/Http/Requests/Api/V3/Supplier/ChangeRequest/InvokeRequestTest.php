<?php

namespace Tests\Unit\Http\Requests\Api\V3\Supplier\ChangeRequest;

use App\Constants\RequestKeys;
use App\Constants\SupplierChangeRequestReasons;
use App\Http\Requests\Api\V3\Supplier\ChangeRequest\InvokeRequest;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_reason()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REASON]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::REASON]),
        ]);
    }

    /** @test */
    public function its_reason_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REASON => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REASON]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::REASON]),
        ]);
    }

    /** @test */
    public function its_reason_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REASON => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REASON]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => RequestKeys::REASON]),
        ]);
    }

    /** @test */
    public function it_requires_a_detail_when_reason_is_other()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::REASON => SupplierChangeRequestReasons::REASON_OTHER]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DETAIL]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::DETAIL]),
        ]);
    }

    /** @test */
    public function its_detail_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DETAIL => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DETAIL]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::DETAIL]),
        ]);
    }

    /** @test */
    public function it_should_limit_the_detail_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DETAIL => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DETAIL]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::DETAIL, 'max' => '1000']),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::REASON => SupplierChangeRequestReasons::REASON_NOT_REAL,
            RequestKeys::DETAIL => 'a valid detail',
        ]);

        $request->assertValidationPassed();
    }
}
