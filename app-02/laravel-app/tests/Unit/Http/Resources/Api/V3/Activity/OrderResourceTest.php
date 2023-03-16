<?php

namespace Tests\Unit\Http\Resources\Api\V3\Activity;

use App\Http\Resources\Api\V3\Activity\OrderResource;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Request;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid fake');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name fake');
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturn($workingOnIt = 'John Doe');
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($updatedAt = $createdAt);
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');

        $resource = new OrderResource($order);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'            => $id,
            'name'          => $name,
            'status'        => $status,
            'working_on_it' => $workingOnIt,
            'created_at'    => $createdAt,
            'updated_at'    => $updatedAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid fake');
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = Carbon::now());
        $order->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($updatedAt = $createdAt);
        $order->shouldReceive('getStatusName')->withNoArgs()->once()->andReturn($status = 'pending');

        $resource = new OrderResource($order);
        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'            => $id,
            'name'          => null,
            'status'        => $status,
            'working_on_it' => null,
            'created_at'    => $createdAt,
            'updated_at'    => $updatedAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
