<?php

namespace Tests\Unit\Http\Resources\Api\V3\OrderSupplier;

use App\Http\Resources\Api\V3\OrderSupplier\SupplierHourResource;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class SupplierHourResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $day              = 'monday';
        $timeZoneSupplier = 'Europe/London';
        $from             = '09:30 am';
        $to               = '05:30 pm';

        $supplierMock = Mockery::mock(Supplier::class);
        $supplierMock->shouldReceive('getAttribute')->withArgs(['timezone'])->twice()->andReturn($timeZoneSupplier);

        $supplierHour = Mockery::mock(SupplierHour::class);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['day'])->once()->andReturn($day);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['from'])->once()->andReturn($from);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['to'])->once()->andReturn($to);
        $supplierHour->shouldReceive('getAttribute')->withArgs(['supplier'])->twice()->andReturn($supplierMock);

        $resource = new SupplierHourResource($supplierHour);

        $response = $resource->resolve();

        $data = [
            'from' => Carbon::parse($day . ' ' . $from, $timeZoneSupplier)->utc()->toISOString(),
            'to'   => Carbon::parse($day . ' ' . $to, $timeZoneSupplier)->utc()->toISOString(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierHourResource::jsonSchema(), false, false);

        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
