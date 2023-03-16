<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DemoStaffSeeder::class,
            DevelopmentUsersSeeder::class,
            LayoutsSeeder::class,
            DemoSuppliesSeeder::class,
            DemoSOPSeeder::class,
            AppSettingsSeeder::class,
            LevelsSeeder::class,
            SettingsSeeder::class,
            TermsSeeder::class,
            AppVersionsSeeder::class,
            NoteCategoriesSeeder::class,
            NotesSeeder::class,
            StateTimezonesSeeder::class,
            InstrumentSupportCallCategoriesSeeder::class,
            ForbiddenZipCodeSeeder::class,
            ModelTypesSeeder::class,
        ]);
    }
}
