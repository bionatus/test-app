<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByAirtableId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByAirtableIdTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_airtable_id()
    {
        $supplier = Supplier::factory()->createQuietly(['airtable_id' => $airtableId = 10001]);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new ByAirtableId($airtableId))->first();

        $this->assertInstanceOf(Supplier::class, $filtered);
        $this->assertSame($supplier->getKey(), $filtered->getKey());
    }
}
