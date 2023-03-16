<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Oem\StoreRequest;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see OemController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_oem_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::OEM])]);
    }

    /** @test */
    public function its_oem_parameter_must_exist_in_database()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => 'fake-oem-uuid']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::OEM])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::OEM => Oem::factory()->create()->getRouteKey(),
        ]);

        $request->assertValidationPassed();
    }
}
