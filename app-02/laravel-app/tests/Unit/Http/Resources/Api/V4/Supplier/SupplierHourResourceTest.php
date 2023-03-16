<?php

namespace Tests\Unit\Http\Resources\Api\V4\Supplier;

use App\Http\Resources\Api\V4\Supplier\SupplierHourResource;
use App\Types\SupplierWorkingHour;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SupplierHourResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $supplierHour = \Mockery::mock(SupplierWorkingHour::class);
        $supplierHour->shouldReceive('date')->withNoArgs()->once()->andReturn($date = '15/01/2023');
        $supplierHour->shouldReceive('from')->withNoArgs()->once()->andReturn($from = '9:00 am');
        $supplierHour->shouldReceive('to')->withNoArgs()->once()->andReturn($to = '5:00 pm');
        $supplierHour->shouldReceive('timezone')->withNoArgs()->once()->andReturn($timeZone = 'America/Chicago');

        $resource = new SupplierHourResource($supplierHour);
        $response = $resource->resolve();

        $data = [
            'from' => Carbon::createFromFormat('d/m/Y H:i A', $date . $from, $timeZone)->utc()->toISOString(),
            'to'   => Carbon::createFromFormat('d/m/Y H:i A', $date . $to, $timeZone)->utc()->toISOString(),
        ];
        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(SupplierHourResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
