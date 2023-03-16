<?php

namespace Tests\Feature\Api\V3\Post;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\Post\Created;
use App\Http\Controllers\Api\V3\PostController;
use App\Http\Requests\Api\V3\Post\StoreRequest;
use App\Http\Resources\Api\V3\Post\BaseResource;
use App\Jobs\LogActivity;
use App\Models\IsTaggable;
use App\Models\Model;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Config;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Image;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PostController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_POST_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_stores_a_post()
    {
        $user     = User::factory()->create();
        $series   = Series::factory()->create();
        $message  = $this->faker->text(100);
        $videoUrl = $this->faker->url;
        $route    = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::MESSAGE   => $message,
            RequestKeys::TAGS      => [
                $series->toTagType()->toArray(),
            ],
            RequestKeys::VIDEO_URL => $videoUrl,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame(0, $data->get('total_comments'));
        $this->assertSame($data->get('message'), $message);
        $this->assertDatabaseHas(Post::tableName(), [
            'uuid'      => $data->get('id'),
            'message'   => $message,
            'type'      => Post::TYPE_OTHER,
            'video_url' => $videoUrl,
        ]);
    }

    /** @test
     * @throws Exception
     */
    public function it_assign_tags_to_a_post_on_creation()
    {
        $series       = Series::factory()->create();
        $morePlainTag = PlainTag::factory()->more()->create();
        $user         = User::factory()->create();
        $message      = $this->faker->text(100);
        $route        = URL::route($this->routeName);

        $this->login($user);

        $tags = new Collection();
        $tags->push($series->toTagType()->toArray());
        $tags->push($morePlainTag->toTagType()->toArray());

        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => $tags->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(Post::tableName(), 1);

        $createdPost   = Post::first();
        $postTagsCount = $createdPost->tags()->count();

        $this->assertEquals($tags->count(), $postTagsCount);

        foreach ($tags as $rawTag) {
            $assignedTag = $createdPost->tags->first(function(Tag $tag) use ($rawTag) {
                /** @var IsTaggable|Model $taggable */
                $taggable = $tag->taggable;

                return $rawTag['id'] == $taggable->getRouteKey() && $rawTag['type'] === $taggable->morphType();
            });
            $this->assertNotNull($assignedTag);
        }
    }

    /** @test
     * @throws Exception
     */
    public function it_can_upload_photos_on_creation()
    {
        Bus::fake();
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [$series->toTagType()->toArray()],
            RequestKeys::IMAGES  => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        /** @var Post $post */
        $post = Post::first();

        $this->assertTrue($post->hasMedia(MediaCollectionNames::IMAGES));

        $media = $post->getFirstMedia(MediaCollectionNames::IMAGES);

        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test
     * @throws Exception
     */
    public function it_creates_a_thumbnail_of_the_uploaded_photos()
    {
        Bus::fake();
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg', 2000, 3000);

        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [$series->toTagType()->toArray()],
            RequestKeys::IMAGES  => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        /** @var Post $post */
        $post = Post::first();

        $this->assertTrue($post->hasMedia(MediaCollectionNames::IMAGES));

        $media = $post->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertTrue($media->hasGeneratedConversion(MediaConversionNames::THUMB));

        $pathGenerator = PathGeneratorFactory::create($media);

        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $conversion         = new Conversion(MediaConversionNames::THUMB);
        $conversionFileName = $conversion->getConversionFile($media);

        Storage::disk($diskName)->assertExists($pathGenerator->getPathForConversions($media) . $conversionFileName);

        $thumb            = Image::make($media->getPath(MediaConversionNames::THUMB));
        $mediaConversions = Config::get('media-conversions');
        $this->assertTrue($thumb->width() <= $mediaConversions[Post::MORPH_ALIAS][MediaConversionNames::THUMB]['width']);
        $this->assertTrue($thumb->height() <= $mediaConversions[Post::MORPH_ALIAS][MediaConversionNames::THUMB]['height']);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_an_activity_log_job()
    {
        Bus::fake();

        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [
                $series->toTagType()->toArray(),
            ],
        ]);

        Bus::assertDispatched(LogActivity::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatches_a_post_created_event()
    {
        Event::fake([Created::class]);

        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [
                $series->toTagType()->toArray(),
            ],
        ]);

        Event::assertDispatched(Created::class);
    }

    /**
     * @test
     *
     * @dataProvider postTypeDataProvider
     * @throws Exception
     */
    public function it_stores_a_post_with_type(string $postType)
    {
        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TYPE    => $postType,
            RequestKeys::TAGS    => [
                $series->toTagType()->toArray(),
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame(0, $data->get('total_comments'));
        $this->assertSame($data->get('message'), $message);
        $this->assertSame($data->get('type'), $postType);
        $this->assertDatabaseHas(Post::tableName(), [
            'uuid'    => $data->get('id'),
            'message' => $message,
            'type'    => $postType,
        ]);
    }

    public function postTypeDataProvider(): array
    {
        return [
            [Post::TYPE_FUNNY],
            [Post::TYPE_NEEDS_HELP],
            [Post::TYPE_OTHER],
        ];
    }

    /** @test
     * @throws Exception
     */
    public function it_set_other_as_type_if_no_type_is_provided()
    {
        $user    = User::factory()->create();
        $series  = Series::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName);

        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::TAGS    => [
                $series->toTagType()->toArray(),
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame(0, $data->get('total_comments'));
        $this->assertSame($data->get('message'), $message);
        $this->assertSame($data->get('type'), Post::TYPE_OTHER);
        $this->assertDatabaseHas(Post::tableName(), [
            'uuid'    => $data->get('id'),
            'message' => $message,
            'type'    => Post::TYPE_OTHER,
        ]);
    }
}
