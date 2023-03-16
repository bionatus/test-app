<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Supplier\Channel;

use App\Http\Resources\Api\V3\Account\Supplier\Channel\OrderResource;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'order uuid');
        $order->shouldReceive('getAttribute')->withArgs(['updated_at'])->once()->andReturn($updatedAt = 'date');

        $resource = new OrderResource($order);
        $response = $resource->resolve();
        $data     = [
            'id'         => $uuid,
            'updated_at' => $updatedAt,
        ];
        $schema   = $this->jsonSchema(OrderResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
