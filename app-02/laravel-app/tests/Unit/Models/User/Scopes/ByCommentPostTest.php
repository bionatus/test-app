<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\User\Scopes\ByCommentPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCommentPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_comments_on_a_post()
    {
        $post       = Post::factory()->create();
        $authorPost = $post->user;

        $firstComment       = Comment::factory()->usingPost($post)->create();
        $authorFirstComment = $firstComment->user;

        $secondComment       = Comment::factory()->usingPost($post)->create();
        $authorSecondComment = $secondComment->user;

        $thirdComment       = Comment::factory()->usingPost($post)->create();
        $authorThirdComment = $thirdComment->user;

        $otherUser = User::factory()->create();

        $filteredUsers    = User::scoped(new ByCommentPost($post))->get();
        $filteredUserKeys = $filteredUsers->pluck(User::keyName())->toArray();

        $expectedUserKeys = [
            $authorFirstComment->id,
            $authorSecondComment->id,
            $authorThirdComment->id,
        ];

        $this->assertEqualsCanonicalizing($expectedUserKeys, $filteredUserKeys);
        $this->assertNotContains($authorPost, $filteredUsers);
        $this->assertNotContains($otherUser, $filteredUsers);
    }
}
