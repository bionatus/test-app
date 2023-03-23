<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\StateTimezone;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\StateTimezonesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class StateTimezonesSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(StateTimezonesSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_state_timezones()
    {
        (new StateTimezonesSeeder())->run();

        foreach (StateTimezonesSeeder::STATE_TIMEZONES as $stateTimezone) {
            $this->assertDatabaseHas(StateTimezone::tableName(), $stateTimezone);
        }
    }

    /** @test */
    public function it_runs_in_all_environments_but_testing()
    {
        $environments = (new StateTimezonesSeeder())->environments();

        $this->assertSame(Environments::ALL_BUT_TESTING, $environments);
    }
}
