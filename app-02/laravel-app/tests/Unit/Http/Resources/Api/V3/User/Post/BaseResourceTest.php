<?php

namespace Tests\Unit\Http\Resources\Api\V3\User\Post;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V2\ImageCollection;
use App\Http\Resources\Api\V2\Post\TagCollection;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\Api\V3\User\Post\BaseResource;
use App\Http\Resources\Api\V3\User\Post\CommentResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('getAttribute')->with('state');
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(123);
        $user->shouldReceive('fullname')->withNoArgs()->once()->andReturn('Full Name user');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('Johnny');
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturn('City user');
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturn('Company user');
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturn(10);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturn(null);
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturnFalse();

        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $post->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = 'a name');
        $post->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'type');
        $post->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $post->shouldReceive('getAttribute')->with('pinned')->once()->andReturnFalse();
        $post->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $post->shouldReceive('getAttribute')->with('comments_count')->once()->andReturn(0);
        $post->shouldReceive('getAttribute')->with('votes_count')->once()->andReturn(0);
        $post->shouldReceive('getAttribute')->with('authUserVote')->once()->andReturnFalse();
        $post->shouldReceive('getAttribute')->with('video_url')->once()->andReturnNull();
        $post->shouldReceive('getAttribute')->with('tags')->times(3)->andReturn(Collection::make([]));
        $post->shouldReceive('isSolved')->once()->andReturnFalse();
        $post->shouldReceive('getMedia')
            ->with(MediaCollectionNames::IMAGES)
            ->once()
            ->andReturn(new MediaCollection([]));
        $post->shouldReceive('loadMissingCount')->with('comments')->once()->andReturnSelf();
        $post->shouldReceive('loadMissingCount')->with('votes')->once()->andReturnSelf();

        $response = (new BaseResource($post))->resolve();

        $data = [
            'id'               => $uuid,
            'message'          => $message,
            'video_url'        => null,
            'type'             => $type,
            'created_at'       => $createdAt,
            'total_comments'   => 0,
            'voted'            => false,
            'votes_count'      => 0,
            'solution_comment' => null,
            'pinned'           => false,
            'user'             => new UserResource($post->user),
            'tags'             => new TagCollection($post->tags),
            'images'           => new ImageCollection(new MediaCollection([])),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->twice()->andReturn(123);
        $user->shouldReceive('fullname')->withNoArgs()->once()->andReturn('Full Name user');

        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('First name Test');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('Last name Test');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $user->shouldReceive('getAttribute')->with('public_name')->twice()->andReturn('Johnny');
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturn('City user');
        $user->shouldReceive('getAttribute')->with('state')->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturn(CountryDataType::UNITED_STATES);
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturn('Company user');
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturn(10);
        $user->shouldReceive('getAttribute')->with('photo')->twice()->andReturn(null);
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturnFalse();

        $comment = Mockery::mock(Comment::class);
        $comment->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(123);
        $comment->shouldReceive('getAttribute')->with('message')->once()->andReturn('Message test');
        $comment->shouldReceive('getAttribute')->with('solution')->once()->andReturn('Solution test');
        $comment->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getUrl')->with('thumb')->once()->andReturn('media thumb url');
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnTrue();

        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'uuid');
        $post->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = 'a name');
        $post->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'type');
        $post->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $post->shouldReceive('getAttribute')->with('pinned')->once()->andReturnTrue();
        $post->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $post->shouldReceive('getAttribute')->with('comments_count')->once()->andReturn($commentsCount = 1);
        $post->shouldReceive('getAttribute')->with('votes_count')->once()->andReturn($votesCount = 1);
        $post->shouldReceive('getAttribute')->with('authUserVote')->once()->andReturnTrue();
        $post->shouldReceive('getAttribute')
            ->with('video_url')
            ->once()
            ->andReturn($videoUrl = 'https://www.videourl.com');
        $post->shouldReceive('getAttribute')->with('tags')->times(3)->andReturn(Collection::make([]));
        $post->shouldReceive('getAttribute')->with('solutionComment')->twice()->andReturn($comment);
        $post->shouldReceive('isSolved')->once()->andReturnTrue();
        $post->shouldReceive('getMedia')
            ->with(MediaCollectionNames::IMAGES)
            ->twice()
            ->andReturn(new MediaCollection([$media]));
        $post->shouldReceive('loadMissingCount')->with('comments')->once()->andReturnSelf();
        $post->shouldReceive('loadMissingCount')->with('votes')->once()->andReturnSelf();

        $response = (new BaseResource($post))->resolve();

        $data = [
            'id'               => $uuid,
            'message'          => $message,
            'type'             => $type,
            'created_at'       => $createdAt,
            'total_comments'   => $commentsCount,
            'voted'            => true,
            'votes_count'      => $votesCount,
            'video_url'        => $videoUrl,
            'solution_comment' => new CommentResource($post->solutionComment),
            'pinned'           => true,
            'user'             => new UserResource($post->user),
            'tags'             => new TagCollection($post->tags),
            'images'           => new ImageCollection($post->getMedia(MediaCollectionNames::IMAGES)),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
