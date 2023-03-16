<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Supplier\BulkHour;

use App\Http\Resources\LiveApi\V1\Supplier\BulkHour\BaseResource;
use App\Models\SupplierHour;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $supplierHour = Mockery::mock(SupplierHour::class);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['day'])->once()->andReturn($day = 'monday');
        $supplierHour->shouldReceive('getAttribute')->withArgs(['from'])->once()->andReturn($from = '09:30');
        $supplierHour->shouldReceive('getAttribute')->withArgs(['to'])->once()->andReturn($to = '17:30');

        $resource = new BaseResource($supplierHour);

        $response = $resource->resolve();

        $data = [
            'day'  => $day,
            'from' => $from,
            'to'   => $to,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);

        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
