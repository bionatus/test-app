<?php

namespace Tests\Unit\Database\Seeders\Settings\User;

use App\Models\Setting;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\Settings\User\AgentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class AgentSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(AgentSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_settings()
    {
        $seeder = new AgentSeeder();
        $seeder->run();

        foreach (AgentSeeder::SETTINGS as $settingData) {
            $this->assertDatabaseHas(Setting::tableName(), $settingData);
        }
    }
}
