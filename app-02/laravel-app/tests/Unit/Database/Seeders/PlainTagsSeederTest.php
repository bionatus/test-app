<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Filesystem;
use App\Models\PlainTag;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\PlainTagsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Storage;
use Tests\TestCase;

class PlainTagsSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(PlainTagsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_stores_all_type_tags()
    {
        Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $seeder = new PlainTagsSeeder();
        $seeder->run();

        foreach (PlainTagsSeeder::TYPE_NAME_LIST as $type => $names) {
            foreach ($names as $name) {
                $this->assertDatabaseHas(PlainTag::tableName(), [
                    'type' => $type,
                    'name' => $name,
                ]);
            }
        }
    }
}
