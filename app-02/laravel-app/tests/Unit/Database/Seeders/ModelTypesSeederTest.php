<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Filesystem;
use App\Models\ModelType;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\ModelTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Storage;
use Tests\TestCase;


class ModelTypesSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_all_model_types()
    {
        Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $seeder = new ModelTypesSeeder();
        $seeder->run();

        foreach (ModelTypesSeeder::MODEL_TYPES_LIST as $name) {
            $this->assertDatabaseHas(ModelType::tableName(), [
                'name' => $name,
            ]);
        }
    }
}
