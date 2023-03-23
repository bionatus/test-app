<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\PlainTag;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see \App\Nova\Resources\PlainTag */
class PlainTagTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/nova-api/' . \App\Nova\Resources\PlainTag::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function it_displays_a_list_of_tags_with_type_more()
    {
        $tags = PlainTag::factory()->more()->count(10)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $tags);
    }

    /** @test */
    public function it_creates_a_tag_with_type_more()
    {
        $plainTagFields = Collection::make([
            'name' => 'A tag title',
            'type' => 'more',
        ]);

        $response = $this->postJson($this->path, $plainTagFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(PlainTag::tableName(), $plainTagFields->toArray());
    }

    /** @test */
    public function it_updates_a_tag_with_type_more()
    {
        $plainTag       = PlainTag::factory()->more()->create();
        $plainTagFields = Collection::make([
            'name' => 'A tag title',
            'type' => 'more',
        ]);

        $response = $this->putJson($this->path . $plainTag->getKey(), $plainTagFields->toArray());

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(PlainTag::tableName(), $plainTagFields->put('id', $plainTag->getKey())->toArray());

        $this->assertDatabaseHas(PlainTag::tableName(), [
            'id'   => $plainTag->getKey(),
            'name' => 'A tag title',
            'type' => PlainTag::TYPE_MORE,
        ]);
    }

    /** @test */
    public function it_uploads_a_photo_in_media_collection()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);

        $file = UploadedFile::fake()->image('avatar.jpeg');

        $plainTag = PlainTag::factory()->more()->create();

        $fieldsToUpdate = Collection::make([
            'name' => 'A tag title',
            'type' => 'more',
        ])->put('__media__', [MediaCollectionNames::IMAGES => [$file]])->toArray();

        $response = $this->put($this->path . $plainTag->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

        $this->assertTrue($plainTag->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $plainTag->getMedia(MediaCollectionNames::IMAGES));

        $media = $plainTag->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_replaces_a_photo_in_media_collection()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);

        $file = UploadedFile::fake()->image('avatar.jpeg');

        $plainTag = PlainTag::factory()->more()->create();
        $oldImage = $plainTag->addMedia($file)
            ->preservingOriginal()
            ->usingName('old_avatar.jpeg')
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $fieldsToUpdate = Collection::make([
            'name' => 'A tag title',
            'type' => 'more',
        ])->put('__media__', [MediaCollectionNames::IMAGES => [$file]])->toArray();

        $response = $this->put($this->path . $plainTag->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();

        $this->assertTrue($plainTag->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $plainTag->getMedia(MediaCollectionNames::IMAGES));

        $media = $plainTag->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $this->assertDeleted($oldImage);
        $pathGenerator = PathGeneratorFactory::create($oldImage);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($oldImage) . $oldImage->file_name);
    }

    /** @test */
    public function it_does_not_destroy_a_plain_tag()
    {
        $plainTag = PlainTag::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $plainTag->getKey());

        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelExists($plainTag);
    }
}
