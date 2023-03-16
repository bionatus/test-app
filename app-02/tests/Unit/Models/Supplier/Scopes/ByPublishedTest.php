<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByPublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByPublishedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_only_unverified_or_verified_and_published()
    {
        $notVerified          = Supplier::factory()->count(2)->unverified()->createQuietly();
        $verifiedAndPublished = Supplier::factory()->count(3)->verified()->published()->createQuietly();

        Supplier::factory()->verified()->unpublished()->count(4)->createQuietly();

        $suppliers = Supplier::scoped(new ByPublished())->get();

        $expected = $notVerified->count() + $verifiedAndPublished->count();

        $this->assertEquals($expected, $suppliers->count());
    }
}
