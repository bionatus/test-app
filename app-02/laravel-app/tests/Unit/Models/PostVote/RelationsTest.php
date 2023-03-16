<?php

namespace Tests\Unit\Models\PostVote;

use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property PostVote $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = PostVote::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_post()
    {
        $related = $this->instance->post()->first();

        $this->assertInstanceOf(Post::class, $related);
    }
}
