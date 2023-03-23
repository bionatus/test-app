<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\Settings\Staff\NotificationSeeder as StaffNotificationSeeder;
use Database\Seeders\Settings\Supplier\NotificationSeeder as SupplierNotificationSeeder;
use Database\Seeders\Settings\Supplier\ValidationSeeder as SupplierValidationSeeder;
use Database\Seeders\Settings\User\AgentSeeder as UserAgentSeeder;
use Database\Seeders\Settings\User\NotificationSeeder as UserNotificationSeeder;
use Database\Seeders\SettingsSeeder;
use Mockery;
use Tests\TestCase;

class SettingsSeederTest extends TestCase
{
    /** @test */
    public function it_execute_seeder()
    {
        $seeder = Mockery::mock(SettingsSeeder::class);
        $seeder->makePartial();
        $seeder->shouldReceive('call')->with([
            StaffNotificationSeeder::class,
            SupplierNotificationSeeder::class,
            SupplierValidationSeeder::class,
            UserAgentSeeder::class,
            UserNotificationSeeder::class,
        ])->once();
        $seeder->run();
    }
}
