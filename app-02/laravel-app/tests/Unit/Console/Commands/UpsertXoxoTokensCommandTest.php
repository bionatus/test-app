<?php

namespace Tests\Unit\Console\Commands;

use App\Exceptions\XoxoException;
use App\Models\ServiceToken;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpsertXoxoTokensCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_throws_an_exception_when_refresh_token_is_empty()
    {
        $this->expectException(XoxoException::class);
        $this->expectExceptionMessage('Refresh token is required');

        $this->artisan('xoxo:upsert-tokens')->assertSuccessful();
    }

    /** @test */
    public function it_does_not_update_token_when_it_has_a_valid_refresh_token_and_the_expiration_is_longer_than_one_week(
    )
    {
        ServiceToken::factory()->create([
            'expired_at' => Carbon::now()->addDays(8),
            'value'      => 'refresh_token',
        ]);

        $this->artisan('xoxo:upsert-tokens')->assertSuccessful();

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => 'refresh_token',
        ]);
    }

    /** @test
     * @dataProvider dataProviderDatetime
     */
    public function it_updates_tokens_when_refresh_token_is_expired_or_is_coming_to_expire_inside_the_week(
        string $datetime
    ) {
        Carbon::setTestNow('2022-11-05 10:00:00');

        Config::set('xoxo.domain', 'http://example.com');
        Config::set('xoxo.client_id', 'client_id');
        Config::set('xoxo.client_secret', 'client_secret');

        ServiceToken::factory()->create([
            'expired_at' => $datetime,
            'value'      => 'old_refresh_token',
        ]);

        $response = Http::response([
            'access_token'  => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);

        Http::fake(['http://example.com/v1/oauth/token/user' => $response]);

        $this->artisan('xoxo:upsert-tokens')->assertSuccessful();

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => 'new_refresh_token',
            'expired_at'   => Carbon::now()->addDays(30),
        ]);

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
            'value'        => 'new_access_token',
            'expired_at'   => Carbon::now()->addDays(15),
        ]);
    }

    public function dataProviderDatetime(): array
    {
        return [
            ['2022-11-05 10:00:00'],
            ['2022-11-12 10:00:00'],
        ];
    }

    /** @test */
    public function it_updates_refresh_token_when_is_provided_via_console()
    {
        Carbon::setTestNow('2022-11-05 10:00:00');

        $newRefreshToken = 'new_refresh_token';

        $this->artisan('xoxo:upsert-tokens --refresh-token=' . $newRefreshToken)->assertSuccessful();

        $this->assertDatabaseHas(ServiceToken::tableName(), [
            'service_name' => ServiceToken::XOXO,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
            'value'        => $newRefreshToken,
            'expired_at'   => '2022-12-05 10:00:00',
        ]);
    }
}
