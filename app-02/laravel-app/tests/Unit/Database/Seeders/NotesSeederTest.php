<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\Note;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\NoteCategoriesSeeder;
use Database\Seeders\NotesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class NotesSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(NotesSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_notes()
    {
        $seeder = new NoteCategoriesSeeder();
        $seeder->run();
        $seeder = new NotesSeeder();
        $seeder->run();

        $noteData = [
            'title' => 'Get parts, get paid!',
            'slug'  => 'gamification-note',
            'body'  => 'Get $20 for every $1,000 you spend on parts + truck stock',
            'link'  => 'https://bluon.com/',
        ];

        $this->assertDatabaseHas(Note::tableName(), $noteData);
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new NotesSeeder();
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
