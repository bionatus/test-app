<?php

namespace Tests\Feature\Api\V2\Post\Comment;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V2\Post\Comment\UpdateRequest;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Models\Comment;
use Config;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

/** @see CommentController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;
    use WithFaker;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_POST_COMMENT_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->patch($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::COMMENT]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_can_update_its_message()
    {
        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [$comment->post, $comment]);
        $message = $this->faker->text(100);

        $this->login($comment->user);
        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $message,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $comment->getRouteKey());
        $this->assertEquals($data['message'], $message);
    }

    /** @test */
    public function it_persist_changes_to_the_db()
    {
        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [$comment->post, $comment]);
        $message = $this->faker->text(100);

        $this->login($comment->user);
        $this->patch($route, [
            RequestKeys::MESSAGE => $message,
        ]);
        $storedComment = Comment::find($comment->getKey());

        $this->assertNotEquals($comment->message, $storedComment->message);
        $this->assertEquals($message, $storedComment->message);
    }

    /** @test */
    public function it_should_not_update_a_comment_that_does_not_belongs_to_a_post()
    {
        $this->withoutExceptionHandling();
        $this->expectException(ModelNotFoundException::class);

        $commentOne = Comment::factory()->create();
        $commentTwo = Comment::factory()->create();
        $route      = URL::route($this->routeName, [
            RouteParameters::POST    => $commentOne->post,
            RouteParameters::COMMENT => $commentTwo,
        ]);

        $this->login($commentTwo->user);

        $this->patch($route);
    }

    /** @test */
    public function it_should_add_images_to_a_comment_without_images()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $comment = Comment::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::IMAGES         => [$file],
            RequestKeys::CURRENT_IMAGES => [],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($comment->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(1, $comment->getMedia(MediaCollectionNames::IMAGES));

        $media         = $comment->getFirstMedia(MediaCollectionNames::IMAGES);
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

        $comment         = Comment::factory()->create();
        $imageToPreserve = $comment->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);
        $imageToDelete   = $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::CURRENT_IMAGES => [$imageToPreserve->uuid],
        ]);
        $comment->refresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($comment->hasMedia(MediaCollectionNames::IMAGES));

        $this->assertCount(1, $comment->getMedia(MediaCollectionNames::IMAGES));
        $this->assertEquals($imageToPreserve->uuid, $comment->getFirstMedia(MediaCollectionNames::IMAGES)->uuid);

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

        $comment = Comment::factory()->create();
        $comment->addMedia($file)->preservingOriginal()->toMediaCollection(MediaCollectionNames::IMAGES);
        $comment->addMedia($file)->toMediaCollection(MediaCollectionNames::IMAGES);

        $mediaCollection = $comment->getMedia(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::CURRENT_IMAGES => null,
        ]);
        $comment->refresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertFalse($comment->hasMedia(MediaCollectionNames::IMAGES));

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

        $comment         = Comment::factory()->create();
        $imageToPreserve = $comment->addMedia($file)
            ->usingName('imageToPreserve')
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);
        $imageToDelete   = $comment->addMedia($file)
            ->usingName('imageToDelete')
            ->preservingOriginal()
            ->toMediaCollection(MediaCollectionNames::IMAGES);

        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::IMAGES         => [$file],
            RequestKeys::CURRENT_IMAGES => [$imageToPreserve->uuid],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue($comment->hasMedia(MediaCollectionNames::IMAGES));
        $this->assertCount(2, $comment->getMedia(MediaCollectionNames::IMAGES));

        $pathGenerator = PathGeneratorFactory::create($imageToDelete);

        $this->assertDeleted($imageToDelete);
        Storage::disk($diskName)->assertMissing($pathGenerator->getPath($imageToDelete) . $imageToDelete->file_name);

        Storage::disk($diskName)->assertExists($pathGenerator->getPath($imageToPreserve) . $imageToPreserve->file_name);

        $newImage = $comment->getMedia(MediaCollectionNames::IMAGES)->where('name', 'avatar')->first();
        Storage::disk($diskName)->assertExists($pathGenerator->getPath($newImage) . $newImage->file_name);

        $commentImagesResource = Collection::make($response->json('data.images.data'));
        $currentImagesUuids    = [$imageToPreserve->uuid, $newImage->uuid];

        $this->assertEqualsCanonicalizing($currentImagesUuids, $commentImagesResource->pluck('id')->toArray());
    }
}
