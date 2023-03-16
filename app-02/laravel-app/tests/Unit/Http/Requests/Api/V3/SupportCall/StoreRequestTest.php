<?php

namespace Tests\Unit\Http\Requests\Api\V3\SupportCall;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\SupportCallController;
use App\Http\Requests\Api\V3\SupportCall\StoreRequest;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Models\SupportCallCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SupportCallController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_category()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CATEGORY]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::CATEGORY])]);
    }

    /** @test */
    public function its_type_must_be_a_valid_category()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CATEGORY => 'foo']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CATEGORY]);
        $request->assertValidationMessages(['Invalid support call category.']);
    }

    /** @test */
    public function it_requires_an_oem_when_the_category_is_oem()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CATEGORY => 'oem']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::OEM]),
        ]);
    }

    /** @test */
    public function its_oem_is_prohibited_if_the_category_is_not_oem()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY => 'foo',
            RequestKeys::OEM      => 'bar',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited_unless', [
                'attribute' => RequestKeys::OEM,
                'other'     => RequestKeys::CATEGORY,
                'values'    => SupportCall::CATEGORY_OEM,
            ]),
        ]);
    }

    /** @test */
    public function its_oem_parameter_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY => 'oem',
            RequestKeys::OEM      => 'bar',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::OEM])]);
    }

    /** @test */
    public function it_pass_on_valid_data_with_oem_category()
    {
        $oem     = Oem::factory()->create();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY => 'oem',
            RequestKeys::OEM      => $oem->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_with_other_category()
    {
        $supportCallCategory = SupportCallCategory::factory()->create();
        $request             = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY => $supportCallCategory->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }
}
