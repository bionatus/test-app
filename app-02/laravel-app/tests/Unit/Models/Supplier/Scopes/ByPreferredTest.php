<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier\Scopes\ByPreferred;
use App\Models\SupplierUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByPreferredTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_preferred()
    {
        SupplierUser::factory()->count(3)->createQuietly();
        $preferred = SupplierUser::factory()->createQuietly(['preferred' => true]);
        $ordered   = SupplierUser::scoped(new ByPreferred())->get();
        $this->assertSame($preferred->getKey(), $ordered->first()->getKey());
    }
}
