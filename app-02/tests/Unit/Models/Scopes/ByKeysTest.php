<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Scopes\ByKeys;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByKeysTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_keys_on_supplier_model()
    {
        Supplier::factory()->count(2)->createQuietly();
        $models = Supplier::factory()->count(3)->createQuietly();

        $filtered = Supplier::scoped(new ByKeys($models->pluck(Supplier::keyName())))->get();

        $this->assertEqualsCanonicalizing($models->pluck(Supplier::keyName())->toArray(),
            $filtered->pluck(Supplier::keyName())->toArray());
    }
}
