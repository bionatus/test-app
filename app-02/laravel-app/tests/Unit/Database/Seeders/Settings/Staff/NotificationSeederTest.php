<?php

namespace Tests\Unit\Database\Seeders\Settings\Staff;

use App\Models\Setting;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\Settings\Supplier\NotificationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class NotificationSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(NotificationSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_settings()
    {
        $seeder = new NotificationSeeder();
        $seeder->run();

        foreach (NotificationSeeder::SETTINGS as $settingData) {
            $this->assertDatabaseHas(Setting::tableName(), $settingData);
        }
    }
}
