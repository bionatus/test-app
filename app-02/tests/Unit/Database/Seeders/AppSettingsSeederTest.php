<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\AppSetting;
use Database\Seeders\AppSettingsSeeder;
use Database\Seeders\EnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class AppSettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(AppSettingsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_settings()
    {
        $seeder = new AppSettingsSeeder();
        $seeder->run();

        foreach (AppSettingsSeeder::SETTINGS as $settingData) {
            $this->assertDatabaseHas(AppSetting::tableName(), $settingData);
        }
    }
}
