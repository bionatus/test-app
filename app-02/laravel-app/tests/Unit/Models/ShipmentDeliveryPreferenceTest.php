<?php

namespace Tests\Unit\Models;

use App\Models\ShipmentDeliveryPreference;
use ReflectionException;
use Spatie\Sluggable\HasSlug;

class ShipmentDeliveryPreferenceTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ShipmentDeliveryPreference::tableName(), [
            'id',
            'slug',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_has_slug_trait()
    {
        $this->assertUseTrait(ShipmentDeliveryPreference::class, HasSlug::class, ['getSlugOptions']);
    }

    /** @test */
    public function it_uses_slug_as_route_key()
    {
        $shipmentDeliveryPreference = ShipmentDeliveryPreference::factory()->create(['slug' => 'something']);

        $this->assertEquals($shipmentDeliveryPreference->slug, $shipmentDeliveryPreference->getRouteKey());
    }
}
