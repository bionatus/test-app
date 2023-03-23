<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\ByRouteKeys;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByRouteKeysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_route_key_on_supplier_model()
    {
        Supplier::factory()->count(2)->createQuietly();
        $models = Supplier::factory()->count(3)->createQuietly();

        $filtered = Supplier::scoped(new ByRouteKeys($models->pluck(Supplier::routeKeyName())))->get();

        $this->assertEqualsCanonicalizing($models->pluck(Supplier::keyName())->toArray(),
            $filtered->pluck(Supplier::keyName())->toArray());
    }
}
