<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\Post;
use App\Models\Post\Scopes\PinnedFirst;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinnedFirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_pinned_first()
    {
        Post::factory()->count(5)->create();
        $expected = Post::factory()->pinned()->create();
        Post::factory()->count(5)->create();

        $this->assertEquals($expected->id, Post::scoped(new PinnedFirst())->get()->first()->id);
    }
}
