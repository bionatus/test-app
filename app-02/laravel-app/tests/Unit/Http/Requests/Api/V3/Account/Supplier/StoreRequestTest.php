<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\Account\SupplierController;
use App\Http\Requests\Api\V3\Account\Supplier\StoreRequest;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SupplierController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_supplier_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_supplier_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SUPPLIER);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_supplier_parameter_must_exist_in_database()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => 'fake-supplier-uuid']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $supplier = Supplier::factory()->createQuietly();
        $request  = $this->formRequest($this->requestClass, [
            RequestKeys::SUPPLIER => $supplier->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }
}
