<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\BulkSupplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\Account\BulkSupplierController;
use App\Http\Requests\Api\V3\Account\BulkSupplier\InvokeRequest;
use App\Models\Supplier;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see BulkSupplierController */
class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_suppliers_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIERS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SUPPLIERS])]);
    }

    /** @test */
    public function its_suppliers_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIERS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIERS]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::SUPPLIERS])]);
    }

    /** @test */
    public function its_preferred_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PREFERRED => ['Lorem Value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PREFERRED]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::PREFERRED])]);
    }

    /** @test */
    public function its_preferred_parameter_must_exist()
    {
        $this->refreshDatabaseForSingleTest();
        $request = $this->formRequest($this->requestClass, [RequestKeys::PREFERRED => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PREFERRED]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::PREFERRED])]);
    }

    /** @test */
    public function its_preferred_parameter_must_exist_on_supplier_parameter()
    {
        $this->refreshDatabaseForSingleTest();
        $supplier          = Supplier::factory()->createQuietly();
        $supplierPreferred = Supplier::factory()->createQuietly();
        $request           = $this->formRequest($this->requestClass, [
            RequestKeys::SUPPLIERS => [$supplier->getRouteKey()],
            RequestKeys::PREFERRED => $supplierPreferred->getRouteKey(),
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PREFERRED]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::PREFERRED])]);
    }

    /** @test */
    public function each_item_in_suppliers_must_exist()
    {
        $this->refreshDatabaseForSingleTest();
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIERS => ['invalid']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIERS . '.0']);
        $request->assertValidationMessages([InvokeRequest::MESSAGE_SUPPLIERS_ALL_EXISTS]);
    }

    /** @test */
    public function it_throws_error_trying_to_get_suppliers_before_validating()
    {
        $request = new InvokeRequest();

        $this->expectError();

        $request->suppliers();
    }

    /** @test */
    public function it_returns_suppliers_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $request  = InvokeRequest::create('', 'POST', [
            RequestKeys::SUPPLIERS => [$supplier->getRouteKey()],
        ]);

        $this->assertEquals([$supplier->getRouteKey()],
            $request->suppliers()->pluck(Supplier::routeKeyName())->toArray());
    }
}
