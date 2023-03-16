<?php

namespace Tests\Unit\Actions\Models\User;

use App\Actions\Models\User\GetTimezone;
use App\Models\StateTimezone;
use App\Models\User;
use App\Models\ZipTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GetTimezoneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_null_when_the_country_is_null()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturnNull();

        $timezone = (new GetTimezone($user))->execute();

        $this->assertNull($timezone);
    }

    /** @test */
    public function it_returns_null_when_the_state_or_zip_exists_but_the_country_does_not_exist_in_any_of_the_timezones_tables(
    )
    {
        StateTimezone::factory()->create([
            'country' => $country = 'country',
            'state'   => $state = 'state',
        ]);
        ZipTimezone::factory()->create([
            'country' => $country,
            'zip'     => $zip = 'zip',
        ]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn('AR');
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state);
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn($zip);

        $timezone = (new GetTimezone($user))->execute();

        $this->assertNull($timezone);
    }

    /** @test */
    public function it_returns_null_when_the_country_exists_but_the_state_or_zip_does_not_exist_in_any_of_the_timezones_tables(
    )
    {
        StateTimezone::factory()->create([
            'country' => $country = 'country',
            'state'   => 'state',
        ]);
        ZipTimezone::factory()->create([
            'country' => $country,
            'zip'     => 'zip',
        ]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country);
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn('other state');
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn('other zip');

        $timezone = (new GetTimezone($user))->execute();

        $this->assertNull($timezone);
    }

    /** @test */
    public function it_returns_the_timezone_when_the_state_and_the_country_exist_in_the_state_timezones_table()
    {
        StateTimezone::factory()->create([
            'country'  => $country = 'country',
            'state'    => $state = 'state',
            'timezone' => $expectedTimezone = 'timezone',
        ]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country);
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state);
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturnNull();

        $timezone = (new GetTimezone($user))->execute();

        $this->assertSame($expectedTimezone, $timezone);
    }

    /** @test */
    public function it_returns_the_timezone_when_the_zip_and_the_country_exist_in_the_zip_timezones_table()
    {
        ZipTimezone::factory()->create([
            'country'  => $country = 'country',
            'zip'      => $zip = 'zip',
            'timezone' => $expectedTimezone = 'timezone',
        ]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country);
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn($zip);

        $timezone = (new GetTimezone($user))->execute();

        $this->assertSame($expectedTimezone, $timezone);
    }

    /** @test */
    public function it_prefers_state_timezone_over_zip_timezone()
    {
        StateTimezone::factory()->create([
            'country'  => $country = 'country',
            'state'    => $state = 'state',
            'timezone' => $stateTimezone = 'state timezone',
        ]);
        ZipTimezone::factory()->create([
            'country' => $country,
            'zip'     => $zip = 'zip',
        ]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['country'])->once()->andReturn($country);
        $user->shouldReceive('getAttribute')->withArgs(['state'])->once()->andReturn($state);
        $user->shouldReceive('getAttribute')->withArgs(['zip'])->once()->andReturn($zip);

        $timezone = (new GetTimezone($user))->execute();

        $this->assertEquals($stateTimezone, $timezone);
    }
}
