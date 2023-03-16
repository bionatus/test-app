<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOnTheNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByOnTheNetworkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_only_verified_and_published()
    {
        $verifiedAndPublished = Supplier::factory()->verified()->published()->createQuietly();
        Supplier::factory()->verified()->unpublished()->count(2)->createQuietly();
        Supplier::factory()->unverified()->published()->count(1)->createQuietly();
        Supplier::factory()->unverified()->unpublished()->count(1)->createQuietly();

        $suppliers = Supplier::scoped(new ByOnTheNetwork())->get();
        $this->assertSame($verifiedAndPublished->getKey(), $suppliers->first()->getKey());
    }
}
