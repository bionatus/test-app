<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\AppSettingsSeeder;
use Database\Seeders\AppVersionsSeeder;
use Database\Seeders\DemoSOPSeeder;
use Database\Seeders\DemoStaffSeeder;
use Database\Seeders\DemoSuppliesSeeder;
use Database\Seeders\DevelopmentSeeder;
use Database\Seeders\DevelopmentUsersSeeder;
use Database\Seeders\ForbiddenZipCodeSeeder;
use Database\Seeders\InstrumentSupportCallCategoriesSeeder;
use Database\Seeders\LayoutsSeeder;
use Database\Seeders\LevelsSeeder;
use Database\Seeders\ModelTypesSeeder;
use Database\Seeders\NoteCategoriesSeeder;
use Database\Seeders\NotesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\StateTimezonesSeeder;
use Database\Seeders\TermsSeeder;
use Mockery;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    /** @test */
    public function it_execute_seeder()
    {
        $seeder = Mockery::mock(DevelopmentSeeder::class);
        $seeder->makePartial();
        $seeder->shouldReceive('call')->with([
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
        ])->once();
        $seeder->run();
    }
}
