<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\Post;
use App\Models\Post\Scopes\ByPinned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByPinnedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_pinned()
    {
        Post::factory()->count(3)->create();
        $expected = Post::factory()->pinned()->count(2)->create();

        $filtered = Post::scoped(new ByPinned(true))->get();

        $this->assertEqualsCanonicalizing($expected->modelKeys(), $filtered->modelKeys());
    }

    /** @test */
    public function it_filters_by_unpinned()
    {
        $expected = Post::factory()->count(3)->create();
        Post::factory()->pinned()->count(2)->create();

        $filtered = Post::scoped(new ByPinned(false))->get();

        $this->assertEqualsCanonicalizing($expected->modelKeys(), $filtered->modelKeys());
    }
}
