<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\NoteCategory;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\NoteCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class NoteCategoriesSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(NoteCategoriesSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_note_categories()
    {
        $seeder = new NoteCategoriesSeeder();
        $seeder->run();

        foreach (NoteCategoriesSeeder::NOTE_CATEGORIES as $noteCategoryData) {
            $this->assertDatabaseHas(NoteCategory::tableName(), $noteCategoryData);
        }
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new NoteCategoriesSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
            Environments::PRODUCTION,
        ];
        $this->assertEquals($expected, $seeder->environments());
    }
}
