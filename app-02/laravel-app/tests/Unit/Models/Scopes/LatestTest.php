<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\InternalNotification;
use App\Models\Post;
use App\Models\Scopes\Latest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LatestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_newest_creation_and_id_on_post_model()
    {
        $posts = Post::factory()->count(3)->sequence(fn($sequence) => [
            'created_at' => Carbon::now()->subDay()->addSeconds($sequence->index),
        ])->create();

        $postsWithSameCreationDate = Post::factory()->count(3)->create();

        $latest = $postsWithSameCreationDate->reverse()->merge($posts->reverse())->values();

        $this->assertEquals($latest->pluck('id'), Post::scoped(new Latest())->get()->pluck('id'));
    }

    /** @test */
    public function it_orders_by_newest_creation_and_id_on_internal_notification_model()
    {
        $internalNotifications = InternalNotification::factory()->count(3)->sequence(fn($sequence) => [
            'created_at' => Carbon::now()->subDay()->addSeconds($sequence->index),
        ])->create();

        $internalNotificationsWithSameCreationDate = InternalNotification::factory()->count(3)->create();

        $latest = $internalNotificationsWithSameCreationDate->reverse()
            ->merge($internalNotifications->reverse())
            ->values();

        $this->assertEquals($latest->pluck('id'), InternalNotification::scoped(new Latest())->get()->pluck('id'));
    }
}
