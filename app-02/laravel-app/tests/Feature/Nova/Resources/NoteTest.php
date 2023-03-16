<?php

namespace Tests\Feature\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\RoutePrefixes;
use App\Models\Note;
use App\Models\NoteCategory;
use App\Nova\Resources\Note as NoteResource;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

/** @see NoteResource */
class NoteTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = '/' . RoutePrefixes::NOVA_API . '/' . NoteResource::uriKey() . '/';
    }

    /** @test */
    public function it_displays_a_list_of_notes()
    {
        $notes = Note::factory()->count(40)->create();

        $response = $this->getJson($this->path);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount($response->json('total'), $notes);

        $data = Collection::make($response->json('resources'));

        $firstPageStores = $notes->sortByDesc('id')->values()->take(count($data));

        $this->assertEquals($data->pluck('id.value'), $firstPageStores->pluck('id'),
            'Resources are not in the correct order.');
    }

    /** @test * */
    public function a_note_can_be_retrieved_with_correct_resource_elements()
    {
        $note = Note::factory()->create();

        $response = $this->getJson($this->path . $note->getKey());
        $response->assertStatus(Response::HTTP_OK);
        $fields = [
            [
                'component' => 'id-field',
                'attribute' => 'id',
                'value'     => $note->getKey(),
            ],
            [
                'component' => 'text-field',
                'attribute' => 'title',
                'value'     => $note->title,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'slug',
                'value'     => $note->getRouteKey(),
                'readonly'  => true,
            ],
            [
                'component' => 'belongs-to-field',
                'attribute' => 'noteCategory',
                'value'     => $note->noteCategory->name,
            ],
            [
                'component' => 'advanced-media-library-field',
                'attribute' => MediaCollectionNames::IMAGES,
                'name'      => 'Image',
                'type'      => 'media',
            ],
            [
                'component' => 'text-field',
                'attribute' => 'body',
                'value'     => $note->body,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'link',
                'value'     => $note->link,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'link_text',
                'value'     => $note->link_text,
            ],
            [
                'component' => 'text-field',
                'attribute' => 'sort',
                'value'     => $note->sort,
            ],
        ];
        $this->assertCount(count($fields), $response->json('resource.fields'));

        $response->assertJson([
            'title'    => $note->title,
            'resource' => [
                'id'     => [
                    'value' => $note->getKey(),
                ],
                'fields' => $fields,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_note()
    {
        $noteCategory = NoteCategory::factory()->featured()->create();
        $note         = Note::factory()->usingNoteCategory($noteCategory)->create();

        $fieldsToUpdate = Collection::make([
            'title'        => 'New title',
            'body'         => 'new body',
            'link'         => 'http://link.test',
            'link_text'    => 'new link text',
            'sort'         => rand(1, 5),
            'noteCategory' => $noteCategory->getKey(),
        ]);

        $response = $this->putJson($this->path . $note->getKey(), $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $fieldsToUpdate->put('id', $note->getKey());
        $fieldsToUpdate->put('note_category_id', $noteCategory->getKey());
        $fieldsToUpdate->forget('noteCategory');
        $this->assertDatabaseHas(Note::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_uploads_a_photo_in_media_collection()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('image.jpeg');

        $noteCategory   = NoteCategory::factory()->featured()->create();
        $note           = Note::factory()->usingNoteCategory($noteCategory)->create();
        $fieldsToUpdate = Collection::make([
            'title'        => 'New title',
            'body'         => 'new body',
            'link'         => 'http://link.test',
            'link_text'    => 'new link text',
            'sort'         => rand(1, 5),
            'noteCategory' => $noteCategory->getKey(),
        ])->put('__media__', [MediaCollectionNames::IMAGES => [$file]])->toArray();

        $response = $this->put($this->path . $note->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue($note->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $note->getMedia(MediaCollectionNames::IMAGES));

        $media = $note->getFirstMedia(MediaCollectionNames::IMAGES);
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

        $noteCategory = NoteCategory::factory()->featured()->create();
        $note         = Note::factory()->usingNoteCategory($noteCategory)->create();
        $oldImage     = $note->addMedia($file)
            ->preservingOriginal()
            ->usingName('old_image.jpeg')
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $fieldsToUpdate = Collection::make([
            'title'        => 'New title',
            'body'         => 'new body',
            'link'         => 'http://link.test',
            'link_text'    => 'new link text',
            'sort'         => rand(1, 5),
            'noteCategory' => $noteCategory->getKey(),
        ])->put('__media__', [MediaCollectionNames::IMAGES => [$file]])->toArray();

        $response = $this->put($this->path . $note->getKey(), $fieldsToUpdate);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue($note->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $note->getMedia(MediaCollectionNames::IMAGES));

        $media = $note->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertSame($file->getClientOriginalName(), $media->file_name);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $this->assertDeleted($oldImage);
        $pathGenerator = PathGeneratorFactory::create($oldImage);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($oldImage) . $oldImage->file_name);
    }

    /** @test */
    public function it_creates_a_featured_note()
    {
        $noteCategory   = NoteCategory::factory()->featured()->create();
        $fieldsToUpdate = Collection::make([
            'title'            => 'New title',
            'body'             => 'new body',
            'link'             => 'http://link.test',
            'link_text'        => 'new link text',
            'sort'             => rand(1, 5),
            'noteCategory'     => $noteCategory->getKey(),
            'note_category_id' => $noteCategory->getKey(),
        ]);

        $response = $this->postJson($this->path, $fieldsToUpdate->toArray());
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
        unset($fieldsToUpdate['noteCategory']);
        $this->assertDatabaseHas(Note::tableName(), $fieldsToUpdate->toArray());
    }

    /** @test */
    public function it_destroys_a_note()
    {
        $note = Note::factory()->create();

        $response = $this->deleteJson($this->path . '?resources[]=' . $note->getKey());
        $response->assertStatus(Response::HTTP_OK);

        $this->assertModelMissing($note);
    }
}
