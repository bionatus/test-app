<?php

namespace Tests\Feature\Api\V2\Post\Comment;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\Post\Comment\Created;
use App\Http\Requests\Api\V2\Post\Comment\StoreRequest;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Jobs\LogActivity;
use App\Models\Comment;
use App\Models\Post;
use Config;
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

/** @see CommentController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $route = URL::route($this->routeName, Post::factory()->create());

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_a_comment()
    {
        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, $post);

        $this->login($post->user);

        $response = $this->post($route, [RequestKeys::MESSAGE => $message]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data->get('message'), $message);
    }

    /** @test */
    public function it_dispatches_a_post_replied_event()
    {
        Event::fake([Created::class]);

        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, $post);

        $this->login($post->user);
        $this->post($route, [RequestKeys::MESSAGE => $message]);

        Event::assertDispatched(Created::class);
    }

    /** @test */
    public function it_can_upload_photos_on_creation()
    {
        Bus::fake();
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, $post);

        $this->login($post->user);

        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::IMAGES  => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        /** @var Comment $comment */
        $comment = $post->comments->first();
        $this->assertTrue($comment->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $comment->getMedia(MediaCollectionNames::IMAGES));

        $media         = $comment->getFirstMedia(MediaCollectionNames::IMAGES);
        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);
    }

    /** @test */
    public function it_creates_a_thumbnail_of_the_uploaded_photo()
    {
        Bus::fake();

        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg', 2000, 3000);

        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, $post);

        $this->login($post->user);

        $response = $this->post($route, [
            RequestKeys::MESSAGE => $message,
            RequestKeys::IMAGES  => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        /** @var Comment $comment */
        $comment = $post->comments->first();
        $this->assertTrue($comment->hasMedia(MediaCollectionNames::IMAGES));

        $media = $comment->getFirstMedia(MediaCollectionNames::IMAGES);
        $this->assertTrue($media->hasGeneratedConversion(MediaConversionNames::THUMB));

        $pathGenerator = PathGeneratorFactory::create($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($media) . $media->file_name);

        $conversion         = new Conversion(MediaConversionNames::THUMB);
        $conversionFileName = $conversion->getConversionFile($media);
        Storage::disk($diskName)->assertExists($pathGenerator->getPathForConversions($media) . $conversionFileName);

        $thumb            = Image::make($media->getPath(MediaConversionNames::THUMB));
        $mediaConversions = Config::get('media-conversions');

        $this->assertTrue($thumb->width() <= $mediaConversions[Comment::MORPH_ALIAS][MediaConversionNames::THUMB]['width']);
        $this->assertTrue($thumb->height() <= $mediaConversions[Comment::MORPH_ALIAS][MediaConversionNames::THUMB]['height']);
    }

    /** @test */
    public function it_should_dispatch_an_activity_log_job()
    {
        Bus::fake();

        $post    = Post::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, $post);

        $this->login($post->user);
        $this->post($route, [RequestKeys::MESSAGE => $message]);

        Bus::assertDispatched(LogActivity::class);
    }
}
