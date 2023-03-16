<?php

namespace Tests\Unit\Database\Seeders;

use App\Models\Brand;
use App\Models\Tag;
use App\Models\UserTaggable;
use Database\Seeders\DeleteBrandSeeder;
use Database\Seeders\EnvironmentSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class DeleteBrandSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(DeleteBrandSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_deletes_brand_tags_and_brand_user_taggables()
    {
        Tag::factory()->count(10)->create([
            'taggable_type' => Brand::MORPH_ALIAS,
        ]);
        UserTaggable::factory()->count(10)->create([
            'taggable_type' => Brand::MORPH_ALIAS,
        ]);

        $seeder = new DeleteBrandSeeder();
        $seeder->run();

        $this->assertDatabaseMissing('tags', ['taggable_type' => Brand::MORPH_ALIAS]);
        $this->assertDatabaseMissing('user_taggable', ['taggable_type' => Brand::MORPH_ALIAS]);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_delete_non_brand_tags_nor_user_taggables()
    {
        Tag::factory()->series()->create();
        Tag::factory()->modelType()->create();
        Tag::factory()->plainTag()->create();
        Tag::factory()->general()->create();
        Tag::factory()->issue()->create();
        Tag::factory()->more()->create();

        UserTaggable::factory()->series()->create();
        UserTaggable::factory()->modelType()->create();
        UserTaggable::factory()->plainTag()->create();
        UserTaggable::factory()->general()->create();
        UserTaggable::factory()->issue()->create();
        UserTaggable::factory()->more()->create();

        $seeder = new DeleteBrandSeeder();
        $seeder->run();

        $this->assertDatabaseCount('tags', 6);
        $this->assertDatabaseCount('user_taggable', 6);
    }
}
