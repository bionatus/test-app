<?php

namespace Tests\Unit\Jobs;

use App\Constants\Filesystem;
use App\Jobs\CopyFilesFromUrlToAssetsDisk;
use App\Models\Part;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Intervention\Image\ImageManager;
use ReflectionClass;
use Storage;
use Tests\TestCase;

/** @see CopyFilesFromUrlToAssetsDisk */
class CopyAirtableFilesToAssetsDiskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CopyFilesFromUrlToAssetsDisk::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new CopyFilesFromUrlToAssetsDisk(Part::class, 'field', 'url');

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_uses_assets_disk_queue()
    {
        $job = new CopyFilesFromUrlToAssetsDisk(Part::class, 'field', 'url');

        $this->assertSame('assets_disk', $job->queue);
    }

    /** @test */
    public function it_does_not_uploads_files_to_assets_disk_when_the_url_does_not_exist_in_the_table()
    {
        $assetsDiskName = Filesystem::DISK_ASSETS;
        $mediaDiskName  = Filesystem::DISK_MEDIA;
        Storage::fake($assetsDiskName);
        Storage::fake($mediaDiskName);

        $field  = 'image';
        $format = 'jpeg';
        $path   = "folder/image.$format";
        $image  = (new ImageManager())->canvas(800, 800)->encode($format);
        Storage::disk($mediaDiskName)->put($path, $image);

        $url = Storage::disk($mediaDiskName)->path($path);

        Part::factory()->count(3)->create();

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();

        $expectedPath = 'parts/image/image.jpeg';
        Storage::disk($assetsDiskName)->assertMissing($expectedPath);
    }

    /** @test */
    public function it_throws_an_exception_when_receive_a_bad_url()
    {
        Part::factory()->create([$field = 'image' => $url = 'bad url']);

        $this->expectException(Exception::class);

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();
    }

    /** @test */
    public function it_uploads_files_to_assets_disk()
    {
        $assetsDiskName = Filesystem::DISK_ASSETS;
        $mediaDiskName  = Filesystem::DISK_MEDIA;
        Storage::fake($assetsDiskName);
        Storage::fake($mediaDiskName);

        $field  = 'image';
        $format = 'jpeg';
        $path   = "folder/image.$format";
        $image  = (new ImageManager())->canvas(800, 800)->encode($format);
        Storage::disk($mediaDiskName)->put($path, $image);

        $part = Part::factory()->create([$field => $url = Storage::disk($mediaDiskName)->path($path)]);

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();
        $part->refresh();

        $expectedPath = 'parts/image/image.jpeg';
        $this->assertEquals(Storage::disk($assetsDiskName)->url($expectedPath), $part->$field);
        Storage::disk($assetsDiskName)->assertExists($expectedPath);
    }

    /** @test */
    public function it_updates_all_registers_that_have_the_same_url()
    {
        $assetsDiskName = Filesystem::DISK_ASSETS;
        $mediaDiskName  = Filesystem::DISK_MEDIA;
        Storage::fake($assetsDiskName);
        Storage::fake($mediaDiskName);

        $field  = 'image';
        $format = 'jpeg';
        $path   = "folder/image.$format";
        $image  = (new ImageManager())->canvas(800, 800)->encode($format);
        Storage::disk($mediaDiskName)->put($path, $image);
        $url = Storage::disk($mediaDiskName)->path($path);

        $part1 = Part::factory()->create([$field => $url]);
        $part2 = Part::factory()->create([$field => "{$url};any string"]);
        $part3 = Part::factory()->create([$field => "any string;{$url}"]);

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();
        $part1->refresh();
        $part2->refresh();
        $part3->refresh();

        $expectedUrl = Storage::disk($assetsDiskName)->url('parts/image/image.jpeg');
        $this->assertEquals($expectedUrl, $part1->$field);
        $this->assertStringContainsString($expectedUrl, $part2->$field);
        $this->assertStringContainsString($expectedUrl, $part3->$field);
    }

    /** @test */
    public function it_serializes_the_file_name_when_the_assets_disk_have_a_file_with_the_same_name()
    {
        $assetsDiskName = Filesystem::DISK_ASSETS;
        $mediaDiskName  = Filesystem::DISK_MEDIA;
        Storage::fake($assetsDiskName);
        Storage::fake($mediaDiskName);

        $field          = 'image';
        $format         = 'jpeg';
        $assetsDiskPath = "parts/$field/image.$format";
        $mediaDiskPath  = "folder/image.$format";
        $image          = (new ImageManager())->canvas(800, 800)->encode($format);
        Storage::disk($mediaDiskName)->put($mediaDiskPath, $image);
        Storage::disk($assetsDiskName)->put($assetsDiskPath, $image);

        $part = Part::factory()->create([$field => $url = Storage::disk($mediaDiskName)->path($mediaDiskPath)]);

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();
        $part->refresh();

        $expectedPath = 'parts/image/image-2.jpeg';
        $this->assertEquals(Storage::disk($assetsDiskName)->url($expectedPath), $part->$field);
        Storage::disk($assetsDiskName)->assertExists($expectedPath);
    }

    /** @test */
    public function it_logs_the_update_queries_executed()
    {
        $assetsDiskName  = Filesystem::DISK_ASSETS;
        $mediaDiskName   = Filesystem::DISK_MEDIA;
        $exportsDiskName = Filesystem::DISK_EXPORTS;
        Storage::fake($assetsDiskName);
        Storage::fake($mediaDiskName);
        Storage::fake($exportsDiskName);

        $field  = 'image';
        $format = 'jpeg';
        $path   = "folder/image.$format";
        $image  = (new ImageManager())->canvas(800, 800)->encode($format);
        Storage::disk($mediaDiskName)->put($path, $image);

        Part::factory()->create([$field => $url = Storage::disk($mediaDiskName)->path($path)]);

        (new CopyFilesFromUrlToAssetsDisk(Part::class, $field, $url))->handle();

        $expectedPath = 'queries-script-airtable-files.sql';
        Storage::disk($exportsDiskName)->assertExists($expectedPath);
        $this->assertStringNotEqualsFile(Storage::disk($exportsDiskName)->path($expectedPath), '');
    }
}
