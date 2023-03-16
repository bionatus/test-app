<?php

namespace Tests\Unit\Http\Requests\Api\V4\Account\Cart;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Account\Cart\UpdateRequest;
use App\Models\Supplier;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

    /** @test */
    public function its_supplier_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_supplier_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => 3]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_supplier_parameter_must_exists()
    {
        $this->refreshDatabaseForSingleTest();
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $this->refreshDatabaseForSingleTest();
        $supplier    = Supplier::factory()->createQuietly();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SUPPLIER => $supplier->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }
}
