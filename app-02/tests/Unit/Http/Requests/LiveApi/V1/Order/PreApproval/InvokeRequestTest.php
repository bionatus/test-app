<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\PreApproval;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V1\Order\SendForApprovalController;
use App\Http\Requests\LiveApi\V1\Order\PreApproval\InvokeRequest;
use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SendForApprovalController */
class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string $requestClass = InvokeRequest::class;
    private Supplier $staff;
    private Setting  $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory()->createQuietly(['email' => 'supplier@email.com']);
        $staff          = Staff::factory()->usingSupplier($this->supplier)->create();
        $this->setting  = Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);

        Auth::shouldReceive('user')->once()->andReturn($staff);
    }

    /** @test */
    public function its_bid_number_parameter_must_be_required_when_setting_supplier_is_true()
    {
        SettingSupplier::factory()
            ->usingSupplier($this->supplier)
            ->usingSetting($this->setting)
            ->create(['value' => true]);
        $request = $this->formRequest($this->requestClass, [RequestKeys::BID_NUMBER => '']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BID_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BID_NUMBER);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_bid_number_parameter_must_not_be_required_when_setting_supplier_is_false()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BID_NUMBER => '']);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_bid_number_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BID_NUMBER => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BID_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BID_NUMBER);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_bid_number_parameter_must_be_a_string_of_no_more_than_24_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::BID_NUMBER => Str::random(25)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::BID_NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::BID_NUMBER);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $attribute,
                'max'       => 24,
            ]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::BID_NUMBER   => Str::random(rand(1, 24)),
        ]);

        $request->assertValidationPassed();
    }
}
