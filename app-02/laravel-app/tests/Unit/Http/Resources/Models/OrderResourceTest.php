<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Http\Resources\Models\OrderResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(OrderResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $uuid        = $this->faker->uuid;
        $name        = $this->faker->text(50);
        $workingOnIt = $this->faker->text(255);
        $createdAt   = $this->faker->date('Y-m-d H:i:s');

        $pickup        = Mockery::mock(Pickup::class);
        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('type')->once()->andReturn('pickup');
        $orderDelivery->shouldReceive('getAttribute')->with('requested_date')->once()->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_start_time')
            ->once()
            ->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')
            ->with('requested_end_time')
            ->once()
            ->andReturn(Carbon::createFromTime(12));
        $orderDelivery->shouldReceive('getAttribute')->with('date')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('fee')->once()->andReturnNull();
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($pickup);
        $orderDelivery->shouldReceive('isWarehouseDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isNeededNow')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isOtherDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isShipmentDelivery')->withNoArgs()->once()->andReturnFalse();
        $orderDelivery->shouldReceive('isPickup')->withNoArgs()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid);
        $order->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->withArgs(['working_on_it'])->once()->andReturn($workingOnIt);
        $order->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($createdAt);

        $resource = new OrderResource($order);
        $response = $resource->resolve();

        $data = [
            'id'            => $uuid,
            'name'          => $name,
            'delivery'      => new OrderDeliveryResource($orderDelivery),
            'working_on_it' => $workingOnIt,
            'created_at'    => $createdAt,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $uuid      = $this->faker->uuid;
        $name      = null;
        $createdAt = $this->faker->date('Y-m-d H:i:s');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid);
        $order->shouldReceive('getAttribute')->with('name')->once()->andReturn($name);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('working_on_it')->once()->andReturnNull();
        $order->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt);

        $resource = new OrderResource($order);
        $response = $resource->resolve();

        $data = [
            'id'            => $uuid,
            'name'          => null,
            'delivery'      => null,
            'working_on_it' => null,
            'created_at'    => $createdAt,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
