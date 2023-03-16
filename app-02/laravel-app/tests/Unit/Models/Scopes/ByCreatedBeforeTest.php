<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\AuthenticationCode;
use App\Models\Comment;
use App\Models\OrderSubstatus;
use App\Models\Post;
use App\Models\Scopes\ByCreatedBefore;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByCreatedBeforeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_creation_date_on_authentication_code_model()
    {
        $date = CarbonImmutable::now();
        AuthenticationCode::factory()->count(2)->create(['created_at' => $date]);
        $expected = AuthenticationCode::factory()->count(3)->create(['created_at' => $date->subSeconds(10)]);

        $authenticationCodes = AuthenticationCode::scoped(new ByCreatedBefore($date->subSeconds(5)))->get();

        $this->assertCount(3, $authenticationCodes);
        $authenticationCodes->each(function(AuthenticationCode $authenticationCode) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $authenticationCode->getKey());
        });
    }

    /** @test */
    public function it_filters_by_creation_date_on_post_model()
    {
        $date = CarbonImmutable::now();
        Post::factory()->count(2)->create(['created_at' => $date]);
        $expected = Post::factory()->count(3)->create(['created_at' => $date->subSeconds(10)]);

        $posts = Post::scoped(new ByCreatedBefore($date->subSeconds(5)))->get();

        $this->assertCount(3, $posts);
        $posts->each(function(Post $post) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $post->getKey());
        });
    }

    /** @test */
    public function it_filters_by_creation_date_on_comment_model()
    {
        $date = CarbonImmutable::now();
        Comment::factory()->count(2)->create(['created_at' => $date]);
        $expected = Comment::factory()->count(3)->create(['created_at' => $date->subSeconds(10)]);

        $comments = Comment::scoped(new ByCreatedBefore($date->subSeconds(5)))->get();

        $this->assertCount(3, $comments);
        $comments->each(function(Comment $comment) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $comment->getKey());
        });
    }

    /** @test */
    public function it_filters_by_creation_date_on_status_model()
    {
        $date = CarbonImmutable::now();
        OrderSubstatus::factory()->count(2)->create(['created_at' => $date]);
        $expected = OrderSubstatus::factory()->count(3)->create(['created_at' => $date->subSeconds(10)]);

        $statuses = OrderSubstatus::scoped(new ByCreatedBefore($date->subSeconds(5)))->get();

        $this->assertCount(3, $statuses);
        $statuses->each(function(OrderSubstatus $status) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $status->getKey());
        });
    }
}
