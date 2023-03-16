<?php

namespace Tests\Unit\Http\Resources\Api\V3\Point\XoxoVoucher;

use App\Http\Resources\Api\V3\Point\XoxoVoucher\DetailedResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\XoxoVoucher;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class DetailResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(DetailedResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $xoxoVoucher = Mockery::mock(XoxoVoucher::class);
        $xoxoVoucher->shouldReceive('getAttribute')
            ->with('value_denominations')
            ->once()
            ->andReturn($valueDenominations = '10,15,20');
        $xoxoVoucher->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'image url');
        $xoxoVoucher->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $xoxoVoucher->shouldReceive('getAttribute')
            ->with('description')
            ->once()
            ->andReturn($description = 'description');
        $xoxoVoucher->shouldReceive('getAttribute')
            ->with('instructions')
            ->once()
            ->andReturn($instructions = 'instructions');
        $xoxoVoucher->shouldReceive('getAttribute')
            ->with('terms_conditions')
            ->once()
            ->andReturn($terms = 'terms and conditions');
        $xoxoVoucher->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1234);

        $response = (new DetailedResource($xoxoVoucher))->resolve();

        $data = [
            'id'                   => $id,
            'name'                 => $name,
            'image'                => $image,
            'value_denominations'  => $valueDenominations,
            'description'          => $description,
            'instructions'         => $instructions,
            'terms_and_conditions' => $terms,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
