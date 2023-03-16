<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifiedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_only_verified()
    {
        Supplier::factory()->count(10)->createQuietly();
        Supplier::factory()->count(3)->verified()->createQuietly();

        $suppliers = Supplier::scoped(new Verified())->get();

        $this->assertCount(3, $suppliers);
    }
}
