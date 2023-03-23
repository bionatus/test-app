<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Supplier\BulkBrand;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Supplier\BulkBrand\StoreRequest;
use App\Models\Brand;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_brands_parameter_should_be_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRANDS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BRANDS);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_brands_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BRANDS => 'string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BRANDS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BRANDS);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_brands_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BRANDS => [['array']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::BRANDS . '.0']);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_brands_parameter_must_exist()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::BRANDS => ['99']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::BRANDS . '.0']);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $brands   = Brand::factory()->count(3)->create();
        $brandIds = $brands->pluck(Brand::routeKeyName())->toArray();

        $request = $this->formRequest($this->requestClass, [RequestKeys::BRANDS => $brandIds]);

        $request->assertValidationPassed();
    }
}
