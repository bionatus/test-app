<?php

namespace Tests\Unit\Http\Requests\Api\V4\SupportCall;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\SupportCallController;
use App\Http\Requests\Api\V4\SupportCall\StoreRequest;
use App\Models\Brand;
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
    public function its_category_must_be_a_valid_category()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CATEGORY => 'foo']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CATEGORY]);
        $request->assertValidationMessages(['Invalid support call category.']);
    }

    /**with OEM*/
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
            RequestKeys::CATEGORY => SupportCall::CATEGORY_OEM,
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
            RequestKeys::CATEGORY => SupportCall::CATEGORY_OEM,
            RequestKeys::OEM      => $oem->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }

    /**with MISSING OEM BRAND*/
    /** @test */
    public function it_requires_an_missing_oem_brand_when_the_category_is_missing_oem()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::CATEGORY => SupportCall::CATEGORY_MISSING_OEM]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_BRAND]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_BRAND);

        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function it_requires_an_missing_oem_model_number_when_the_category_is_missing_oem()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::CATEGORY => SupportCall::CATEGORY_MISSING_OEM]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_MODEL_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_MODEL_NUMBER);

        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_missing_oem_brand_is_prohibited_if_the_category_is_not_missing_oem()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY          => 'foo',
            RequestKeys::MISSING_OEM_BRAND => 'bar',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_BRAND]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_BRAND);

        $request->assertValidationMessages([
            Lang::get('validation.prohibited_unless', [
                'attribute' => $attribute,
                'other'     => RequestKeys::CATEGORY,
                'values'    => SupportCall::CATEGORY_MISSING_OEM,
            ]),
        ]);
    }

    /** @test */
    public function its_missing_oem_model_number_is_prohibited_if_the_category_is_not_missing_oem()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY                 => SupportCall::CATEGORY_OEM,
            RequestKeys::MISSING_OEM_MODEL_NUMBER => 'fake number',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_MODEL_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_MODEL_NUMBER);

        $request->assertValidationMessages([
            Lang::get('validation.prohibited_unless', [
                'attribute' => $attribute,
                'other'     => RequestKeys::CATEGORY,
                'values'    => SupportCall::CATEGORY_MISSING_OEM,
            ]),
        ]);
    }

    /** @test */
    public function its_missing_oem_brand_parameter_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY                 => SupportCall::CATEGORY_MISSING_OEM,
            RequestKeys::MISSING_OEM_BRAND        => 'bar',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_BRAND]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_BRAND);

        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_missing_oem_model_number_parameter_must_be_string()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY                 => SupportCall::CATEGORY_MISSING_OEM,
            RequestKeys::MISSING_OEM_MODEL_NUMBER => [1, 2],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MISSING_OEM_MODEL_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MISSING_OEM_MODEL_NUMBER);

        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_pass_on_valid_data_with_missing_oem_brand_category()
    {
        $brand   = Brand::factory()->create();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CATEGORY                 => SupportCall::CATEGORY_MISSING_OEM,
            RequestKeys::MISSING_OEM_BRAND        => $brand->getRouteKey(),
            RequestKeys::MISSING_OEM_MODEL_NUMBER => 'fake model number',
        ]);

        $request->assertValidationPassed();
    }

    /**OTHER*/
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
