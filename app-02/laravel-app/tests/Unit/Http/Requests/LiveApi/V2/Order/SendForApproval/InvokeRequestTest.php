<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\SendForApproval;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V2\Order\SendForApprovalController;
use App\Http\Requests\LiveApi\V2\Order\SendForApproval\InvokeRequest;
use App\Models\Setting;
use App\Models\SettingSupplier;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SendForApprovalController */
class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = InvokeRequest::class;
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
    public function its_total_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TOTAL]),
        ]);
    }

    /** @test */
    public function its_total_parameter_must_be_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_total_parameter_must_be_a_number_not_less_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => -1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $attribute,
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_total_parameter_should_have_money_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => '12.345']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([Lang::get('validation.regex', ['attribute' => $attribute])]);
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
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => 1000]);

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
    public function its_note_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NOTE])]);
    }

    /** @test */
    public function its_note_must_be_255_characters_maximum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::NOTE, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TOTAL      => 67.89,
            RequestKeys::BID_NUMBER => Str::random(rand(1, 24)),
            RequestKeys::NOTE       => 'Fake note',
        ]);

        $request->assertValidationPassed();
    }
}
