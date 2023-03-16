<?php

namespace Tests\Feature\Api\V2\Post;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Requests\Api\V2\Post\UpdateRequest;
use App\Http\Resources\Api\V2\Post\BaseResource;
use App\Models\IsTaggable;
use App\Models\Model;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use Config;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PostController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;
    use WithFaker;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName, Post::factory()->create()));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::POST]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_can_update_its_message()
    {
        $post    = Post::factory()->create();
        $route   = URL::route($this->routeName, $post);
        $message = $this->faker->text(100);
        $series  = Series::factory()->create();

        $this->login($post->user);
        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [$series->toTagType()->toArray()],
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $post->getRouteKey());
        $this->assertSame($data['message'], $message);
        $this->assertSame($post->comments()->count(), $data->get('total_comments'));

        $this->assertDatabaseHas(Post::tableName(), [
            Post::routeKeyName() => $data['id'],
            'message'            => $message,
        ]);
    }

    /** @test
     * @throws Exception
     */
    public function it_persist_changes_to_the_db()
    {
        $post    = Post::factory()->create();
        $route   = URL::route($this->routeName, $post);
        $message = $this->faker->text(100);
        $series  = Series::factory()->create();

        $this->login($post->user);
        $this->patch($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [$series->toTagType()->toArray()],
        ]);

        $storedPost = Post::where('uuid', $post->getRouteKey())->first();

        $this->assertNotEquals($post->message, $storedPost->message);
        $this->assertEquals($message, $storedPost->message);
    }

    /** @test
     * @throws Exception
     */
    public function it_assign_tags_to_a_post_on_edition()
    {
        $post         = Post::factory()->create();
        $series       = Series::factory()->create();
        $morePlainTag = PlainTag::factory()->more()->create();

        $route = URL::route($this->routeName, $post);

        $taggables = new Collection();
        $taggables->push($series);
        $taggables->push($morePlainTag);

        $rawTags = $taggables->map(fn(IsTaggable $taggable) => $taggable->toTagType()->toArray())->toArray();
        $message = $this->faker->text(100);
        $this->login($post->user);
        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => $rawTags,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $tags = $post->tags;
        $this->assertCount($taggables->count(), $tags);
        $taggables->each(function (IsTaggable $taggable) use ($post) {
            $this->assertDatabaseHas(Tag::tableName(), [
                'post_id'       => $post->getKey(),
                'taggable_id'   => $taggable->getKey(),
                'taggable_type' => $taggable->getMorphClass(),
            ]);
        });

        $this->assertEqualsCanonicalizing($rawTags, $response->json('data.tags.data'));
    }

    /** @test
     * @throws Exception
     */
    public function it_updates_the_tags_of_the_post_on_edition()
    {
        $post         = Post::factory()->create();
        $series       = Series::factory()->create();
        $morePlainTag = PlainTag::factory()->more()->create();

        Tag::factory()->usingPost($post)->usingSeries($series)->create();
        Tag::factory()->usingPost($post)->usingPlainTag($morePlainTag)->create();

        $route = URL::route($this->routeName, $post);

        $newSeries     = Series::factory()->create();
        $issuePlainTag = PlainTag::factory()->issue()->create();

        $taggables = new Collection();
        $taggables->push($newSeries);
        $taggables->push($morePlainTag);
        $taggables->push($issuePlainTag);

        $rawTags = $taggables->map(fn(IsTaggable $taggable) => $taggable->toTagType()->toArray())->toArray();
        $message = $this->faker->text(100);
        $this->login($post->user);
        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => $rawTags,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $tags = $post->tags;
        $this->assertCount($taggables->count(), $tags);
        $taggables->each(function (IsTaggable $taggable) use ($post) {
            $this->assertDatabaseHas(Tag::tableName(), [
                'post_id'       => $post->getKey(),
                'taggable_id'   => $taggable->getKey(),
                'taggable_type' => $taggable->getMorphClass(),
            ]);
        });

        $this->assertEqualsCanonicalizing($rawTags, $response->json('data.tags.data'));
    }

    /** @test
     * @throws Exception
     */
    public function it_leaves_tags_untouched_if_same_tags_are_sent()
    {
        $post         = Post::factory()->create();
        $series       = Series::factory()->create();
        $morePlainTag = PlainTag::factory()->more()->create();

        $tagSeries = Tag::factory()->usingPost($post)->usingSeries($series)->create();
        $tagMore   = Tag::factory()->usingPost($post)->usingPlainTag($morePlainTag)->create();

        $route = URL::route($this->routeName, $post);

        $rawTags = new Collection();
        $rawTags->push($series->toTagType()->toArray());
        $rawTags->push($morePlainTag->toTagType()->toArray());

        $message = $this->faker->text(100);
        $this->login($post->user);
        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => $rawTags->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $postTagsCount = $post->tags()->count();

        $this->assertEquals($rawTags->count(), $postTagsCount);

        $rawTags->each(function (array $rawTag) use ($post) {
            $assignedTag = $post->tags->first(function (Tag $tag) use ($rawTag) {
                /** @var IsTaggable|Model $taggable */
                $taggable = $tag->taggable;

                return $rawTag['id'] == $taggable->getRouteKey() && $rawTag['type'] === $taggable->morphType();
            });
            $this->assertNotNull($assignedTag);
        });

        $this->assertDatabaseHas(Tag::tableName(), $tagSeries->toArray());
        $this->assertDatabaseHas(Tag::tableName(), $tagMore->toArray());
    }

    /** @test
     * @throws Exception
     */
    public function it_should_add_images_to_a_post_without_images()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $series  = Series::factory()->create();
        $route   = URL::route($this->routeName, [$post]);

        $this->login($post->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::IMAGES         => [$file],
            RequestKeys::CURRENT_IMAGES => [],
            RequestKeys::TAGS           => [$series->toTagType()->toArray()],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($post->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $post->getMedia(MediaCollectionNames::IMAGES));

        $media         = $post->getFirstMedia(MediaCollectionNames::IMAGES);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function it_should_delete_images_not_sent_in_current_images()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $post            = Post::factory()->create();
        $imageToPreserve = $post->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);
        $imageToDelete   = $post->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $series  = Series::factory()->create();
        $route   = URL::route($this->routeName, [$post]);

        $this->login($post->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::CURRENT_IMAGES => [$imageToPreserve->uuid],
            RequestKeys::TAGS           => [$series->toTagType()->toArray()],
        ]);
        $post->refresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($post->hasMedia(MediaCollectionNames::IMAGES));

        $this->assertCount(1, $post->getMedia(MediaCollectionNames::IMAGES));
        $this->assertEquals($imageToPreserve->uuid, $post->getFirstMedia(MediaCollectionNames::IMAGES)->uuid);

        $this->assertDeleted($imageToDelete);
        $pathGenerator = PathGeneratorFactory::create($imageToDelete);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($imageToDelete) . $imageToDelete->file_name);
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function it_should_delete_all_images_if_current_images_is_null()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $post = Post::factory()->create();
        $post->addMedia($file)->preservingOriginal()->toMediaCollection(MediaCollectionNames::IMAGES);
        $post->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);

        $mediaCollection = $post->getMedia(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $series  = Series::factory()->create();
        $route   = URL::route($this->routeName, [$post]);

        $this->login($post->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::CURRENT_IMAGES => null,
            RequestKeys::TAGS           => [$series->toTagType()->toArray()],
        ]);
        $post->refresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertFalse($post->hasMedia(MediaCollectionNames::IMAGES));

        foreach ($mediaCollection as $media) {
            $pathGenerator = PathGeneratorFactory::create($media);
            $this->assertDeleted($media);
            Storage::disk($diskName)->assertMissing($pathGenerator->getPath($media) . $media->file_name);
        }
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function it_should_add_and_delete_images_according_to_images_and_current_images_parameters()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $post            = Post::factory()->create();
        $imageToPreserve = $post->addMedia($file)
            ->usingName('imageToPreserve')
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);
        $imageToDelete   = $post->addMedia($file)
            ->usingName('imageToDelete')
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $series  = Series::factory()->create();
        $route   = URL::route($this->routeName, [$post]);

        $this->login($post->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::IMAGES         => [$file],
            RequestKeys::CURRENT_IMAGES => [$imageToPreserve->uuid],
            RequestKeys::TAGS           => [$series->toTagType()->toArray()],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($post->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(2, $post->getMedia(MediaCollectionNames::IMAGES));

        $pathGenerator = PathGeneratorFactory::create($imageToDelete);

        $this->assertDeleted($imageToDelete);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($imageToDelete) . $imageToDelete->file_name);

        Storage::disk($diskName)->assertExists($pathGenerator->getPath($imageToPreserve) . $imageToPreserve->file_name);

        $newImage = $post->getMedia(MediaCollectionNames::IMAGES)->where('name', 'avatar')->first();
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($newImage) . $newImage->file_name);

        $postImagesResource = Collection::make($response->json('data.images.data'));
        $currentImagesUuids = [$imageToPreserve->uuid, $newImage->uuid];

        $this->assertEqualsCanonicalizing($currentImagesUuids, $postImagesResource->pluck('id')->toArray());
    }
}
