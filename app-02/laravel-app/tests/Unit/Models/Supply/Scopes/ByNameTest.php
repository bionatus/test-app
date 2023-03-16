<?php

namespace Tests\Unit\Models\Supply\Scopes;

use App\Models\Supply;
use App\Models\Supply\Scopes\ByName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByNameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_name()
    {
        $nameSupply = 'Name Supply Lorem';
        $supply     = Supply::factory()->create(['name' => $nameSupply]);
        Supply::factory()->count(10)->create();

        $filtered = Supply::scoped(new ByName($nameSupply))->first();

        $this->assertInstanceOf(Supply::class, $filtered);
        $this->assertSame($supply->getKey(), $filtered->getKey());
    }
}
