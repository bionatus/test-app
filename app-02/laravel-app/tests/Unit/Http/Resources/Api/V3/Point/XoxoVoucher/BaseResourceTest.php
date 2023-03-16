<?php

namespace Tests\Unit\Http\Resources\Api\V3\Point\XoxoVoucher;

use App\Http\Resources\Api\V3\Point\XoxoVoucher\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\XoxoVoucher;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $xoxoVoucher = Mockery::mock(XoxoVoucher::class);
        $xoxoVoucher->shouldReceive('getAttribute')
            ->with('first_denomination')
            ->once()
            ->andReturn($firstDenomination = 10);
        $xoxoVoucher->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'image url');
        $xoxoVoucher->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $xoxoVoucher->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1234);

        $response = (new BaseResource($xoxoVoucher))->resolve();

        $data = [
            'id'                 => $id,
            'name'               => $name,
            'image'              => $image,
            'first_denomination' => $firstDenomination,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
