<?php

namespace Database\Seeders;

use Database\Seeders\Settings\Staff\NotificationSeeder as StaffNotificationSeeder;
use Database\Seeders\Settings\Supplier\NotificationSeeder as SupplierNotificationSeeder;
use Database\Seeders\Settings\Supplier\ValidationSeeder as SupplierValidationSeeder;
use Database\Seeders\Settings\User\AgentSeeder as UserAgentSeeder;
use Database\Seeders\Settings\User\NotificationSeeder as UserNotificationSeeder;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            StaffNotificationSeeder::class,
            SupplierNotificationSeeder::class,
            SupplierValidationSeeder::class,
            UserAgentSeeder::class,
            UserNotificationSeeder::class,
        ]);
    }
}
