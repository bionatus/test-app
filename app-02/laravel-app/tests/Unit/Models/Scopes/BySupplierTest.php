<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\PubnubChannel;
use App\Models\Scopes\BySupplier;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySupplierTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supplier = Supplier::factory()->createQuietly();
    }

    /** @test */
    public function it_filters_by_supplier_on_pubnub_channel_model()
    {
        $expectedPubnubChannels = PubnubChannel::factory()->usingSupplier($this->supplier)->count(2)->create();

        $anotherSupplier = Supplier::factory()->withEmail()->createQuietly();
        PubnubChannel::factory()->count(3)->usingSupplier($anotherSupplier)->create();

        $this->assertEquals($expectedPubnubChannels->pluck('id'),
            PubnubChannel::scoped(new BySupplier($this->supplier))->pluck('id'));
    }

    /** @test */
    public function it_filters_by_supplier_on_supplier_user_model()
    {
        SupplierUser::factory()->count(3)->createQuietly();
        $expectedSupplierUsers = SupplierUser::factory()->usingSupplier($this->supplier)->count(2)->createQuietly();

        $this->assertEquals($expectedSupplierUsers->pluck('id'),
            SupplierUser::scoped(new BySupplier($this->supplier))->pluck('id'));
    }
}
