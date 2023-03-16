<?php

namespace Tests\Feature\Api\V3\Account\Point\XoxoVoucher\Redeem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\Point\XoxoVoucher\RedeemController;
use App\Http\Requests\Api\V3\Account\Point\XoxoVoucher\Redeem\StoreRequest;
use App\Http\Resources\Api\V3\Account\Point\Redemption\BaseResource;
use App\Models\AppSetting;
use App\Models\Level;
use App\Models\Phone;
use App\Models\Point;
use App\Models\ServiceToken;
use App\Models\User;
use App\Models\XoxoRedemption;
use App\Models\XoxoVoucher;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see RedeemController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_POINTS_VOUCHERS_REDEEM_STORE;

    public function setUp(): void
    {
        parent::setUp();

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        Level::factory()->create([
            'slug'        => 'level-0',
            'from'        => 0,
            'to'          => 999,
            'coefficient' => 0.5,
        ]);

        AppSetting::factory()->create([
            'slug'  => AppSetting::SLUG_BLUON_POINTS_MULTIPLIER,
            'type'  => AppSetting::TYPE_INTEGER,
            'value' => 2,
        ]);

        ServiceToken::create([
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => 'example_refresh_token',
        ]);
        ServiceToken::create([
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
            'value'        => 'example_access_token',
            'expired_at'   => Carbon::now()->addHours(10),
        ]);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $xoxoVoucher = XoxoVoucher::factory()->create();
        $route       = URL::route($this->routeName, [RouteParameters::VOUCHER => $xoxoVoucher->code]);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        $user = User::factory()->create();
        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 10000]);

        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '5,10,25']);
        $route       = URL::route($this->routeName, [RouteParameters::VOUCHER => $xoxoVoucher->getRouteKey()]);

        $this->makeMockXoxoCall();

        $this->login($user);
        $response = $this->post($route, [RequestKeys::DENOMINATION => '25']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_a_xoxo_redeem_and_decrease_available_user_points_to_cash()
    {
        $user = User::factory()->create();

        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 10000]);

        $pointsCashInitial = $user->availablePointsToCash();

        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '5,10,25']);
        $route       = URL::route($this->routeName, [RouteParameters::VOUCHER => $xoxoVoucher->getRouteKey()]);

        $this->makeMockXoxoCall();

        $this->login($user);
        $this->post($route, [RequestKeys::DENOMINATION => $denomination = '25']);

        $this->assertDatabaseCount(XoxoRedemption::tableName(), 1);
        $this->assertDatabaseHas(XoxoRedemption::tableName(), [
            'redemption_code'    => 6214194,
            'voucher_code'       => $xoxoVoucher->getRouteKey(),
            'value_denomination' => $denomination,
            'amount_charged'     => $denomination,
        ]);

        $this->assertDatabaseHas(Point::tableName(), [
            'user_id'         => $user->getKey(),
            'points_redeemed' => 2500,
        ]);

        $expectedPointsCash = $pointsCashInitial - ($denomination);
        $this->assertSame($user->availablePointsToCash(), $expectedPointsCash);
    }

    /** @test */
    public function it_should_not_save_any_data_if_xoxo_call_fails()
    {
        $user = User::factory()->create();

        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 10000]);

        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '5,10,25']);
        $route       = URL::route($this->routeName, [RouteParameters::VOUCHER => $xoxoVoucher->getRouteKey()]);

        Http::fake(['http://example.com/v1/oauth/api' => Http::response('Error', 400)]);

        $this->login($user);
        $this->post($route, [RequestKeys::DENOMINATION => '25']);

        $this->assertDatabaseCount(XoxoRedemption::tableName(), 0);
        $this->assertDatabaseMissing(Point::tableName(), [
            'user_id'         => $user->getKey(),
            'points_redeemed' => 2500,
        ]);
    }

    /** @test */
    public function it_stores_a_xoxo_redeem_using_a_user_with_legacy_phone()
    {
        $user = User::factory()->create(['phone' => '123456']);
        Phone::factory()->usingUser($user)->create();

        Point::factory()->usingUser($user)->createQuietly(['points_earned' => 10000]);

        $xoxoVoucher = XoxoVoucher::factory()->create(['value_denominations' => '5,10,25']);
        $route       = URL::route($this->routeName, [RouteParameters::VOUCHER => $xoxoVoucher->getRouteKey()]);

        $this->makeMockXoxoCall();

        $this->login($user);
        $response = $this->post($route, [RequestKeys::DENOMINATION => '25']);

        $response->assertStatus(Response::HTTP_CREATED);
    }

    private function makeMockXoxoCall()
    {
        $vouchersResponseMockFile    = __DIR__ . '/../../../../../../../Unit/Services/Xoxo/__mock__/placeOrderResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);
    }
}
