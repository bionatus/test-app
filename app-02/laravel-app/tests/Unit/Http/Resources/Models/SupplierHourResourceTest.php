<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\SupplierHourResource;
use App\Models\SupplierHour;
use Mockery;
use Tests\TestCase;

class SupplierHourResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $supplierHour = Mockery::mock(SupplierHour::class);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['day'])->once()->andReturn($day = 'monday');
        $supplierHour->shouldReceive('getAttribute')->withArgs(['from'])->once()->andReturn($from = '09:30');
        $supplierHour->shouldReceive('getAttribute')->withArgs(['to'])->once()->andReturn($to = '17:30');

        $resource = new SupplierHourResource($supplierHour);

        $response = $resource->resolve();

        $data = [
            'day'  => $day,
            'from' => $from,
            'to'   => $to,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierHourResource::jsonSchema(), false, false);

        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
