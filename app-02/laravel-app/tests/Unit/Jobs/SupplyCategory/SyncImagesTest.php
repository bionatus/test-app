<?php

namespace Tests\Unit\Jobs\SupplyCategory;

use App\Constants\Filesystem;
use App\Constants\MediaCollectionNames;
use App\Jobs\SupplyCategory\SyncImages;
use App\Models\Media;
use App\Models\SupplyCategory;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Tests\TestCase;

class SyncImagesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SyncImages::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_sync_supply_category_image_with_parent()
    {
        $parentName   = 'parent-name';
        $parent       = SupplyCategory::factory()->create(['name' => $parentName]);
        $categoryName = 'category-name';
        $category     = SupplyCategory::factory()->usingParent($parent)->create(['name' => $categoryName]);

        $remoteDisk = Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $mediaDisk  = Storage::fake(Config::get('media-library.disk_name'));
        $imagePath  = Filesystem::FOLDER_DEVELOPMENT_COMMON_ITEMS_CAT_IMAGES . $parentName . '-' . $categoryName . '.png';
        $remoteDisk->put($imagePath, 'fake image');

        $job = new SyncImages();
        $job->handle();

        $this->assertTrue($category->hasMedia(MediaCollectionNames::IMAGES));
        $media         = $category->getFirstMedia(MediaCollectionNames::IMAGES);
        $pathGenerator = PathGeneratorFactory::create($media);
        $mediaDisk->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_sync_supply_category_image_without_parent()
    {
        $categoryName = 'category-name';
        $category     = SupplyCategory::factory()->create(['name' => $categoryName]);

        $remoteDisk = Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $mediaDisk  = Storage::fake(Config::get('media-library.disk_name'));
        $imagePath  = Filesystem::FOLDER_DEVELOPMENT_COMMON_ITEMS_CAT_IMAGES . $categoryName . '.png';
        $remoteDisk->put($imagePath, 'fake image');

        $job = new SyncImages();
        $job->handle();

        $this->assertTrue($category->hasMedia(MediaCollectionNames::IMAGES));
        $media         = $category->getFirstMedia(MediaCollectionNames::IMAGES);
        $pathGenerator = PathGeneratorFactory::create($media);
        $mediaDisk->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_sync_supply_category_image_with_image()
    {
        $categoryName = 'category-name';
        $category     = SupplyCategory::factory()->create(['name' => $categoryName]);
        Media::factory()->create(['model_type' => SupplyCategory::MORPH_ALIAS, 'model_id' => $category->id]);

        $remoteDisk = Storage::fake(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $imagePath  = Filesystem::FOLDER_DEVELOPMENT_COMMON_ITEMS_CAT_IMAGES . $categoryName . '.png';
        $remoteDisk->put($imagePath, 'fake image');

        $job = new SyncImages();
        $job->handle();

        $this->assertCount(1, $category->getMedia(MediaCollectionNames::IMAGES));
    }
}
