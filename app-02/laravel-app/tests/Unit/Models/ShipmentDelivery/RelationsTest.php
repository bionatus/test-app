<?php

namespace Tests\Unit\Models\ShipmentDelivery;

use App\Models\Address;
use App\Models\OrderDelivery;
use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ShipmentDelivery $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ShipmentDelivery::factory()->createQuietly();
    }

    /** @test */
    public function it_is_a_order_delivery()
    {
        $related = $this->instance->orderDelivery()->first();

        $this->assertInstanceOf(OrderDelivery::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_destination_address()
    {
        $related = $this->instance->destinationAddress()->first();

        $this->assertInstanceOf(Address::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_shipment_delivery_preference()
    {
        $related = $this->instance->shipmentDeliveryPreference()->first();

        $this->assertInstanceOf(ShipmentDeliveryPreference::class, $related);
    }
}
