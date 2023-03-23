<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class PostTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Post::tableName(), [
            'id',
            'user_id',
            'uuid',
            'message',
            'video_url',
            'type',
            'pinned',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $post = Post::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($post->uuid, $post->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $post = Post::factory()->make(['uuid' => null]);
        $post->save();

        $this->assertNotNull($post->uuid);
    }

    /** @test */
    public function it_knows_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();

        $post = Post::factory()->create();

        $this->assertFalse($post->isOwner($notOwner));
        $this->assertTrue($post->isOwner($post->user));
    }

    /** @test */
    public function it_knows_if_is_solved()
    {
        $post               = Post::factory()->create();
        $notSolutionComment = Comment::factory()->create();
        $solutionComment    = Comment::factory()->solution()->create();

        $this->assertFalse($post->isSolved());
        $this->assertFalse($notSolutionComment->post->isSolved());
        $this->assertTrue($solutionComment->post->isSolved());
    }

    /** @test */
    public function it_is_created_with_default_type()
    {
        $post = new Post();

        $this->assertSame($post->type, Post::TYPE_OTHER);
    }
}
