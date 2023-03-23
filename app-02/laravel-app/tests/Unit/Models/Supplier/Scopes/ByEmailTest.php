<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_email()
    {
        $supplier = Supplier::factory()->createQuietly(['email' => 'search@email.com']);
        Supplier::factory()->count(10)->createQuietly();

        $filtered = Supplier::scoped(new ByEmail('search@email.com'))->first();

        $this->assertInstanceOf(Supplier::class, $filtered);
        $this->assertSame($supplier->getKey(), $filtered->getKey());
    }
}
