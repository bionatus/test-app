<?php

namespace Tests\Unit\Models;

use App\Models\Authenticatable;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Arr;
use Carbon\CarbonImmutable;
use Config;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Mockery;
use ReflectionClass;
use Tymon\JWTAuth\Contracts\JWTSubject;

class PhoneTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Phone::tableName(), [
            'id',
            'user_id',
            'country_code',
            'number',
            'verified_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_is_a_jwt_authenticatable()
    {
        $reflection = new ReflectionClass(Phone::class);
        $parent     = $reflection->getParentClass()->getName();

        $this->assertSame(Authenticatable::class, $parent);
        $this->assertTrue($reflection->implementsInterface(JWTSubject::class));
        $this->assertTrue(Arr::exists($reflection->getTraits(), HasApiTokens::class));
    }

    /** @test */
    public function it_returns_a_full_number_representation()
    {
        $phone = Phone::factory()->make([
            'country_code' => '1',
            'number'       => '555222810',
        ]);

        $this->assertSame('1555222810', $phone->fullNumber());
    }

    /** @test */
    public function it_returns_ttl_minutes_if_is_verified_but_not_assigned()
    {
        Config::set('communications.phone.verification.ttl', 3);
        Carbon::setTestNow('2021-01-01 00:00:00');
        $now = CarbonImmutable::now();

        $phone = Phone::factory()->verified()->create(['created_at' => $now]);

        $this->assertTrue($phone->nextRequestAvailableAt()->eq($now->addMinutes(3)));
    }

    /** @test */
    public function it_does_not_eager_load_authentication_codes()
    {
        Config::set('communications.sms.code.retry_after', [10]);
        Carbon::setTestNow('2021-01-01');
        $phone = Phone::factory()->create();
        $zero  = $phone->nextRequestAvailableAt();
        AuthenticationCode::factory()->usingPhone($phone)->create(['created_at' => Carbon::now()]);

        $this->assertTrue($phone->nextRequestAvailableAt()->eq($zero->addSeconds(10)));
    }

    /** @test
     * @dataProvider nextRequestAvailableAtProvider
     */
    public function it_returns_date_of_next_available_code_request(int $authenticationCodesCount, string $expected)
    {
        Config::set('communications.sms.code.retry_after', [30, 60, 90]);
        Config::set('communications.sms.code.reset_after', 7200);

        Carbon::setTestNow('2021-01-01 00:00:00');
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-01-01 00:00:00');

        $authenticationCodes = AuthenticationCode::factory()->sequence(fn(Sequence $sequence) => [
            'phone_id'   => 1,
            'created_at' => $date->addSeconds($sequence->index * 20),
        ])->count($authenticationCodesCount)->make();

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('get')->withNoArgs()->once()->andReturn($authenticationCodes);

        $phone = Mockery::mock(Phone::class);
        $phone->makePartial();
        $phone->shouldReceive('authenticationCodes')->withNoArgs()->once()->andReturn($hasMany);

        $this->assertSame($expected, $phone->nextRequestAvailableAt()->format('Y-m-d H:i:s'));
    }

    public function nextRequestAvailableAtProvider(): array
    {
        return [
            [0, '2021-01-01 00:00:00'],
            [1, '2021-01-01 00:00:30'],
            [2, '2021-01-01 00:01:20'],
            [3, '2021-01-01 00:02:10'],
            [4, '2021-01-01 02:00:00'],
        ];
    }

    /** @test */
    public function it_knows_if_is_verified()
    {
        $verified   = Phone::factory()->verified()->make();
        $unverified = Phone::factory()->unverified()->make();

        $this->assertTrue($verified->isVerified());
        $this->assertFalse($unverified->isVerified());
    }

    /** @test */
    public function it_knows_if_is_assigned()
    {
        $assigned   = Phone::factory()->make(['user_id' => 1]);
        $unassigned = Phone::factory()->make();

        $this->assertTrue($assigned->isAssigned());
        $this->assertFalse($unassigned->isAssigned());
    }

    /** @test */
    public function it_knows_if_is_verified_and_assigned_to_a_user()
    {
        $verified   = Phone::factory()->verified()->make(['user_id' => 1]);
        $unverified = Phone::factory()->unverified()->make();

        $this->assertTrue($verified->isVerifiedAndAssigned());
        $this->assertFalse($unverified->isVerifiedAndAssigned());
    }

    /** @test */
    public function it_can_verify()
    {
        $phone = Phone::factory()->unverified()->make();

        $this->assertFalse($phone->isVerified());
        $phone->verify();
        $this->assertTrue($phone->isVerified());
    }

    /** @test */
    public function it_returns_full_phone_number_as_sms_channel_token()
    {
        $phone = Phone::factory()->create();

        $this->assertEquals($phone->routeNotificationForSms(), $phone->fullNumber());
    }
}
