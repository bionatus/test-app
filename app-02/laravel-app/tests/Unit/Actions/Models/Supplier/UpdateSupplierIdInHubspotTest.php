<?php

namespace Tests\Unit\Actions\Models\Supplier;

use App;
use App\Actions\Models\Supplier\UpdateSupplierIdInHubspot;
use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UpdateSupplierIdInHubspotTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_call_to_hubspot_method_when_exist_a_supplier_with_hubspot_id()
    {
        $supplier = Supplier::factory()->createQuietly([
            'hubspot_id' => $hubspotId = '123456',
        ]);
        Supplier::factory()->count(10)->createQuietly();
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateCompanySupplierId')->with($supplier->getKey(), $hubspotId)->once();
        App::bind(Hubspot::class, fn() => $hubspot);

        $action = new UpdateSupplierIdInHubspot();
        $action->execute();
    }

    /** @test */
    public function it_does_not_call_to_hubspot_method_when_exist_a_supplier_with_hubspot_id()
    {
        Supplier::factory()->count(10)->createQuietly();
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('updateCompanySupplierId')->withAnyArgs()->never();
        App::bind(Hubspot::class, fn() => $hubspot);

        $action = new UpdateSupplierIdInHubspot();
        $action->execute();
    }
}
