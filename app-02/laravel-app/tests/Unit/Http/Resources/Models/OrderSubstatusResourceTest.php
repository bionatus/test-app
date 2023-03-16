<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OrderSubstatusResource;
use App\Http\Resources\Models\SubstatusResource;
use App\Models\OrderSubstatus;
use App\Models\Status;
use App\Models\Substatus;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class OrderSubstatusResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(OrderSubstatusResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($statusRouteKey = 'pending');

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $substatus->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($substatusRouteKey = 'pending-requested');

        $orderSubStatus = Mockery::mock(OrderSubstatus::class);
        $orderSubStatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);
        $orderSubStatus->shouldReceive('getAttribute')->with('detail')->once()->andReturn($detail = 'Fake detail.');
        $orderSubStatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($now = Carbon::now());

        $resource = new OrderSubstatusResource($orderSubStatus);
        $response = $resource->resolve();

        $data = [
            'status'     => $statusRouteKey,
            'substatus'  => $substatusRouteKey,
            'detail'     => $detail,
            'created_at' => $now,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderSubstatusResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $status = Mockery::mock(Status::class);
        $status->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($statusRouteKey = 'pending');

        $substatus = Mockery::mock(Substatus::class);
        $substatus->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $substatus->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($substatusRouteKey = 'pending-requested');

        $orderSubStatus = Mockery::mock(OrderSubstatus::class);
        $orderSubStatus->shouldReceive('getAttribute')->with('substatus')->once()->andReturn($substatus);
        $orderSubStatus->shouldReceive('getAttribute')->with('detail')->once()->andReturnNull();
        $orderSubStatus->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($now = Carbon::now());

        $resource = new OrderSubstatusResource($orderSubStatus);
        $response = $resource->resolve();

        $data = [
            'status'     => $statusRouteKey,
            'substatus'  => $substatusRouteKey,
            'detail'     => null,
            'created_at' => $now,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OrderSubstatusResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
