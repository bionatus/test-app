<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Point\XoxoVoucher\Redeem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Account\Point\XoxoVoucher\Redeem\StoreRequest;
use App\Models\Order;
use App\Models\Point;
use App\Models\Supplier;
use App\Models\User;
use App\Models\XoxoVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see RedeemController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    protected function setUp(): void
    {
        parent::setUp();

        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '5,10,25']);
        $user        = User::factory()->create();
        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->usingSupplier($supplier)->create();
        Point::factory()->usingUser($user)->usingOrder($order)->create(['points_earned' => 1000]);

        $this->login($user);
        $this->route = URL::route(RouteNames::API_V3_ACCOUNT_POINTS_VOUCHERS_REDEEM_STORE,
            [RouteParameters::VOUCHER => $xoxoVoucher->getRouteKey()]);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route])->assertAuthorized();
    }

    /** @test */
    public function its_denomination_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DENOMINATION]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::DENOMINATION]),
        ]);
    }

    /** @test */
    public function its_denomination_parameter_should_be_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DENOMINATION => 'A String'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DENOMINATION]);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::DENOMINATION]),
        ]);
    }

    /** @test */
    public function its_denomination_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DENOMINATION => 100],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DENOMINATION]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => RequestKeys::DENOMINATION]),
        ]);
    }

    /** @test */
    public function its_has_enough_points_to_redeem()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DENOMINATION => 100],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DENOMINATION]);
        $request->assertValidationMessages(['Not enough funds.']);
    }

    /** @test */
    public function it_should_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DENOMINATION => 5],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
