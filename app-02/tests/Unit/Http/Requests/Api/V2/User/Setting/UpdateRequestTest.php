<?php

namespace Tests\Unit\Http\Requests\Api\V2\User\Setting;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\User\SettingController;
use App\Http\Requests\Api\V2\User\Setting\UpdateRequest;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Route;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SettingController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

    protected function setUp(): void
    {
        parent::setUp();

        Route::model(RouteParameters::SETTING_USER, Setting::class);
    }

    /** @test */
    public function it_requires_a_value()
    {
        $setting = Setting::factory()->create();
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VALUE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::VALUE])]);
    }

    /** @test */
    public function its_value_must_be_a_boolean_on_boolean_type_setting()
    {
        $setting = Setting::factory()->boolean()->create();
        $request = $this->formRequest($this->requestClass, [RequestKeys::VALUE => 'a string'])
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VALUE]);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => RequestKeys::VALUE])]);
    }

    /** @test */
    public function its_value_must_be_a_string_on_string_type_setting()
    {
        $setting = Setting::factory()->string()->create();
        $request = $this->formRequest($this->requestClass, [RequestKeys::VALUE => 1354])
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VALUE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::VALUE])]);
    }

    /** @test */
    public function its_value_must_be_a_integer_on_integer_type_setting()
    {
        $setting = Setting::factory()->integer()->create();
        $request = $this->formRequest($this->requestClass, [RequestKeys::VALUE => 'a string'])
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VALUE]);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => RequestKeys::VALUE])]);
    }

    /** @test */
    public function its_value_must_be_a_double_on_numeric_type_setting()
    {
        $setting = Setting::factory()->double()->create();
        $request = $this->formRequest($this->requestClass, [RequestKeys::VALUE => 'a string'])
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VALUE]);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => RequestKeys::VALUE])]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_data($settingType, $value)
    {
        $setting = Setting::factory()->create(['type' => $settingType]);
        $request = $this->formRequest($this->requestClass, [RequestKeys::VALUE => $value])
            ->addRouteParameter(RouteParameters::SETTING_USER, $setting->getRouteKey());

        $request->assertValidationPassed();
    }

    public function dataProvider(): array
    {
        return [
            [Setting::TYPE_BOOLEAN, true],
            [Setting::TYPE_BOOLEAN, false],
            [Setting::TYPE_DOUBLE, 9.5],
            [Setting::TYPE_INTEGER, 2],
            [Setting::TYPE_STRING, 'foo'],
        ];
    }
}
