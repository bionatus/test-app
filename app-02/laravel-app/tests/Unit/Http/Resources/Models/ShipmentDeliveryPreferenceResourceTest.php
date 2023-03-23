<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ShipmentDeliveryPreferenceResource;
use App\Models\ShipmentDeliveryPreference;
use ReflectionClass;
use Tests\TestCase;

class ShipmentDeliveryPreferenceResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ShipmentDeliveryPreferenceResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $shipmentDeliveryPreference = \Mockery::mock(ShipmentDeliveryPreference::class);
        $shipmentDeliveryPreference->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $resource = new ShipmentDeliveryPreferenceResource($shipmentDeliveryPreference);

        $response = $resource->resolve();

        $data = [
            'id' => $id,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ShipmentDeliveryPreferenceResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
