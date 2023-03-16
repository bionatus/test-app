<?php

namespace Tests\Unit\Observers;

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $post = Post::factory()->make(['uuid' => null]);

        $observer = new PostObserver();

        $observer->creating($post);

        $this->assertNotNull($post->uuid);
    }
}
