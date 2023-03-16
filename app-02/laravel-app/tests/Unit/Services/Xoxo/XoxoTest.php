<?php

namespace Tests\Unit\Services\Xoxo;

use App\Events\Service\Log;
use App\Exceptions\XoxoException;
use App\Models\ServiceToken;
use App\Models\XoxoVoucher as XoxoVoucherModel;
use App\Services\Xoxo\Xoxo;
use App\Types\XoxoVoucher;
use Config;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XoxoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_a_xoxo_exception_when_no_credentials_are_provided()
    {
        Config::set('xoxo.client_id');
        Config::set('xoxo.client_secret');

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('Xoxo credentials are required');
        new Xoxo();
    }

    /** @test */
    public function it_throws_a_xoxo_exception_when_no_domain_is_provided()
    {
        Config::set('xoxo.domain');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('Xoxo domain is required');
        new Xoxo();
    }

    /** @test */
    public function it_throws_a_xoxo_exception_when_refresh_token_is_empty()
    {
        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('Refresh token is required');
        new Xoxo();
    }

    /** @test */
    public function it_throws_a_xoxo_exception_when_access_token_generation_fails()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();

        $response = Http::response([
            'error'             => 'invalid_request',
            'error_description' => 'code expired/invalid',
        ], 403);

        Http::fake(['http://example.com/v1/oauth/token/user' => $response]);

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('{"error":"invalid_request","error_description":"code expired\/invalid"}');

        new Xoxo();
    }

    /** @test */
    public function it_generates_access_token_successfully_if_it_is_not_created()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();

        $response = Http::response([
            'access_token'  => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);

        Http::fake(['http://example.com/v1/oauth/token/user' => $response]);

        new Xoxo();

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
            'value'        => 'new_access_token',
            'expired_at'   => Carbon::now()->addDays(15),
        ]);

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => 'new_refresh_token',
            'expired_at'   => Carbon::now()->addDays(30),
        ]);
    }

    /** @test */
    public function it_generates_a_new_access_token_if_it_is_expired()
    {
        Event::fake(Log::class);

        Carbon::setTestNow('2023-01-01');

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');
        Config::set('xoxo.notify_admin_email', 1);

        ServiceToken::create([
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => 'example_refresh_token',
        ]);
        ServiceToken::create([
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
            'value'        => 'example_access_token',
            'expired_at'   => Carbon::now()->addHours(-10),
        ]);

        $response = Http::response([
            'access_token'  => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);

        Http::fake(['http://example.com/v1/oauth/token/user' => $response]);

        new Xoxo();

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
            'value'        => 'new_access_token',
            'expired_at'   => Carbon::now()->addDays(15),
        ]);
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_calls_get_vouchers_successfully()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();
        ServiceToken::factory()->create([
            'token_name' => ServiceToken::ACCESS_TOKEN,
            'expired_at' => Carbon::now()->addHour(),
        ]);

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/getVouchersResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $xoxo     = new Xoxo();
        $response = $xoxo->getRedeemMethods();

        $this->assertCollectionOfClass(XoxoVoucher::class, $response);
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_throws_a_xoxo_exception_when_returns_an_error()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

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

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/getVouchersErrorResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent, 404);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('{"code":"404","errorId":"PLE10030","errorInfo":"Failed to find vouchers.","error":"No Vouchers Found"}');

        $xoxo = new Xoxo();
        $xoxo->getRedeemMethods();
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_calls_place_order_successfully()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');
        Config::set('xoxo.notify_admin_email', 1);

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

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/placeOrderResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $productId    = 27218;
        $quantity     = 1;
        $denomination = 10;
        $poNumber     = '1234';
        $email        = 'fake-user@example.com';
        $contact      = 'Fake User';

        $xoxo     = new Xoxo();
        $response = $xoxo->redeem($productId, $quantity, $denomination, $poNumber, $email, $contact);

        $expected = $vouchersResponseMockContent['data']['placeOrder']['data'];

        $this->assertEquals($expected, $response);
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_throws_a_xoxo_exception_when_place_order_fails()
    {
        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');
        Config::set('xoxo.notify_admin_email', 1);

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

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/placeOrderErrorResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent, 400);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $productId    = 99999;
        $quantity     = 1;
        $denomination = 10;
        $poNumber     = '4321';
        $email        = 'fake-user@example.com';
        $contact      = 'Fake User';

        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('{"code":"400","errorId":"PLE10001","errorInfo":"Validation error in place order.","error":[{"msg":"Mobile number is missing\/Invalid","failure":true}]}');

        $xoxo = new Xoxo();
        $xoxo->redeem($productId, $quantity, $denomination, $poNumber, $email, $contact);
    }

    /** @test */
    public function it_dispatches_a_log_event_when_tokens_are_generated()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();

        $response = Http::response([
            'access_token'  => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);

        Http::fake(['http://example.com/v1/oauth/token/user' => $response]);

        new Xoxo();

        Event::assertDispatched(Log::class);
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_dispatches_a_log_event_when_get_vouchers()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();
        ServiceToken::factory()->create([
            'token_name' => ServiceToken::ACCESS_TOKEN,
            'expired_at' => Carbon::now()->addHour(),
        ]);

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/getVouchersResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $xoxo = new Xoxo();
        $xoxo->getRedeemMethods();

        Event::assertDispatched(Log::class);
    }

    /** @test */
    public function it_dispatches_a_log_event_when_an_user_redeems()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

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

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/placeOrderResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $productId    = 27218;
        $quantity     = 1;
        $denomination = 10;
        $poNumber     = '1234';
        $email        = 'fake-user@example.com';
        $contact      = 'Fake User';

        $xoxo = new Xoxo();
        $xoxo->redeem($productId, $quantity, $denomination, $poNumber, $email, $contact);

        Event::assertDispatched(Log::class);
    }

    /** @test
     * @throws \App\Exceptions\XoxoException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function it_returns_only_the_vouchers_with_fixed_denomination_type()
    {
        Event::fake(Log::class);

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create();
        ServiceToken::factory()->create([
            'token_name' => ServiceToken::ACCESS_TOKEN,
            'expired_at' => Carbon::now()->addHour(),
        ]);

        $vouchersResponseMockFile    = __DIR__ . '/__mock__/getVouchersResponse.json';
        $vouchersResponseMockContent = json_decode(file_get_contents($vouchersResponseMockFile), true);
        $vouchersResponseMock        = Http::response($vouchersResponseMockContent);
        Http::fake(['http://example.com/v1/oauth/api' => $vouchersResponseMock]);

        $vouchers      = Collection::make($vouchersResponseMockContent['data']['getVouchers']['data']);
        $fixedVouchers = $vouchers->where('valueType', '!==', XoxoVoucherModel::TYPE_OPEN_VALUE);

        $xoxo     = new Xoxo();
        $response = $xoxo->getRedeemMethods();

        $this->assertCollectionOfClass(XoxoVoucher::class, $response);
        $this->assertCount($fixedVouchers->count(), $response);
        $this->assertNotSameSize($vouchers, $response);
    }
}
