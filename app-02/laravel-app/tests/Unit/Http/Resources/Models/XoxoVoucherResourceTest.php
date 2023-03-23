<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\XoxoVoucherResource;
use App\Models\XoxoVoucher;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class XoxoVoucherResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(XoxoVoucherResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $xoxoVoucher = Mockery::mock(XoxoVoucher::class);
        $xoxoVoucher->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'image url');
        $xoxoVoucher->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $xoxoVoucher->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1234);

        $response = (new XoxoVoucherResource($xoxoVoucher))->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => $image,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(XoxoVoucherResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
