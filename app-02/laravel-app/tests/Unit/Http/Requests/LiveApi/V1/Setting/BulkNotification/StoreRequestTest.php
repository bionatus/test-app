<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Setting\BulkNotification;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Setting\BulkNotification\StoreRequest;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_settings_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SETTINGS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SETTINGS])]);
    }

    /** @test */
    public function its_settings_parameter_should_be_an_array_of_valid_supplier_notification_settings()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SETTINGS => '[invalid setting => true]']);
        $request->assertValidationFailed();

        $request->assertValidationErrors([RequestKeys::SETTINGS]);
        $request->assertValidationMessages([Lang::get('validation.custom.array_with_valid_keys')]);
    }

    /** @test */
    public function its_settings_parameter_values_should_be_booleans()
    {
        $setting = Setting::factory()->groupNotification()->applicableToSupplier()->create();
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::SETTINGS => [$setting->getRouteKey() => 'invalid value']]);
        $request->assertValidationFailed();

        $item = RequestKeys::SETTINGS . '.' . $setting->getRouteKey();
        $request->assertValidationErrors([$item]);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => $item])]);
    }

    /** @test */
    public function it_should_pass_on_valid_data()
    {
        $setting = Setting::factory()->groupNotification()->applicableToSupplier()->create();
        Setting::factory()->groupAgent()->count(3)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SETTINGS => [$setting->getRouteKey() => true],
        ]);

        $request->assertValidationPassed();
    }
}

