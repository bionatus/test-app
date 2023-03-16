<?php

namespace Tests\Feature\Api\V3\Post\Comment;

use App\Constants\MediaCollectionNames;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Post\Comment\UserTagged;
use App\Http\Requests\Api\V3\Post\Comment\UpdateRequest;
use App\Http\Resources\Api\V2\Post\Comment\BaseResource;
use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\User;
use Config;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
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

    private string $routeName = RouteNames::API_V3_POST_COMMENT_UPDATE;

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

    /** @test */
    public function it_sync_tagged_users_in_a_comment()
    {
        Event::fake([UserTagged::class]);

        $comment       = Comment::factory()->create();
        $userAttached  = User::factory()->create();
        $userDetached  = User::factory()->create();
        $newTaggedUser = User::factory()->create();

        $route = URL::route($this->routeName, [$comment->post, $comment]);

        CommentUser::factory()->usingUser($userAttached)->usingComment($comment)->create();
        CommentUser::factory()->usingUser($userDetached)->usingComment($comment)->create();

        $taggedUsers = [$userAttached->getKey(), $newTaggedUser->getKey()];

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE => $this->faker->text(100),
            RequestKeys::USERS   => $taggedUsers,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        Collection::make($taggedUsers)->each(function($userId) use ($comment) {
            $this->assertDatabaseHas(CommentUser::tableName(), [
                'user_id'    => $userId,
                'comment_id' => $comment->getKey(),
            ]);
        });

        $this->assertDatabaseMissing(CommentUser::tableName(), [
            'user_id'    => $userDetached->getKey(),
            'comment_id' => $comment->getKey(),
        ]);
    }

    /** @test */
    public function it_dispatches_a_user_tagged_event_for_each_new_tagged_user()
    {
        Event::fake([UserTagged::class]);

        $comment        = Comment::factory()->create();
        $userAttached   = User::factory()->create();
        $newTaggedUser1 = User::factory()->create();
        $newTaggedUser2 = User::factory()->create();
        $route          = URL::route($this->routeName, [$comment->post, $comment]);

        CommentUser::factory()->usingUser($userAttached)->usingComment($comment)->create();

        $taggedUsers = [$userAttached->getKey(), $newTaggedUser1->getKey(), $newTaggedUser2->getKey()];

        $this->login($comment->user);

        $this->patch($route, [
            RequestKeys::MESSAGE => $this->faker->text(100),
            RequestKeys::USERS   => $taggedUsers,
        ]);

        Event::assertDispatched(UserTagged::class);
        Event::assertDispatchedTimes(UserTagged::class, 2);
    }

    /** @test */
    public function it_not_dispatches_a_user_tagged_event_if_are_not_new_tagged_users()
    {
        Event::fake([UserTagged::class]);

        $comment      = Comment::factory()->create();
        $userAttached = User::factory()->create();
        $route        = URL::route($this->routeName, [$comment->post, $comment]);

        CommentUser::factory()->usingUser($userAttached)->usingComment($comment)->create();

        $taggedUsers = [$userAttached->getKey()];

        $this->login($comment->user);

        $this->patch($route, [
            RequestKeys::MESSAGE => $this->faker->text(100),
            RequestKeys::USERS   => $taggedUsers,
        ]);

        Event::assertNotDispatched(UserTagged::class);
    }

    /** @test */
    public function it_updates_content_updated_at_when_add_an_images()
    {
        $diskName = Config::get('media-library.disk_name');
        Storage::fake($diskName);
        $file = UploadedFile::fake()->image('avatar.jpeg');

        $comment = Comment::factory()->create();
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $comment->message,
            RequestKeys::IMAGES         => [$file],
            RequestKeys::CURRENT_IMAGES => [],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue(!!$comment->refresh()->content_updated_at);
    }

    /** @test */
    public function it_updates_content_updated_at_when_message_is_changed()
    {
        $comment = Comment::factory()->create();
        $message = $this->faker->text(100);
        $route   = URL::route($this->routeName, [$comment->post, $comment]);

        $this->login($comment->user);

        $response = $this->patch($route, [
            RequestKeys::MESSAGE        => $message,
            RequestKeys::CURRENT_IMAGES => [],
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $this->assertTrue(!!$comment->refresh()->content_updated_at);
    }
}
