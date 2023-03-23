<?php

namespace Tests\Unit\Database\Seeders\Settings\Supplier;

use App\Models\Setting;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\Settings\Supplier\ValidationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class ValidationSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(ValidationSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_settings()
    {
        $seeder = new ValidationSeeder();
        $seeder->run();

        foreach (ValidationSeeder::SETTINGS as $settingData) {
            $this->assertDatabaseHas(Setting::tableName(), $settingData);
        }
    }
}
