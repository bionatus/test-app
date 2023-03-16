<?php

namespace Tests\Unit\Models\ShipmentDeliveryPreference;

use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ShipmentDeliveryPreference $instance
 */
class RelationsTest extends RelationsTestCase
{
    /** @test */
    public function it_has_shipment_deliveries()
    {
        ShipmentDelivery::factory()->usingShipmentDeliveryPreference($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->shipmentDelivery()->get();

        $this->assertCorrectRelation($related, ShipmentDelivery::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ShipmentDeliveryPreference::factory()->createQuietly();
    }
}
