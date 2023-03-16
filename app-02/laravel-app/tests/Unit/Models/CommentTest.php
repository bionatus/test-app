<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Str;

class CommentTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Comment::tableName(), [
            'id',
            'user_id',
            'post_id',
            'uuid',
            'message',
            'content_updated_at',
            'solution',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $comment = Comment::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($comment->uuid, $comment->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $comment = Comment::factory()->make(['uuid' => null]);
        $comment->save();

        $this->assertNotNull($comment->uuid);
    }

    /** @test */
    public function it_can_determine_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();
        $owner    = User::factory()->create();
        $comment  = Comment::factory()->usingUser($owner)->create();

        $this->assertFalse($comment->isOwner($notOwner));
        $this->assertTrue($comment->isOwner($owner));
    }

    /** @test */
    public function it_can_determine_if_the_comment_is_a_solution_for_his_post()
    {
        $comment = Comment::factory()->create();

        $comment->solution = true;

        $this->assertTrue($comment->isSolution());
    }

    /** @test */
    public function it_can_determine_if_the_comment_is_not_a_solution_for_his_post()
    {
        $comment = Comment::factory()->create();

        $this->assertFalse($comment->isSolution());
    }
}
