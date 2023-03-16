<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Supplier;

use App\Http\Resources\LiveApi\V1\Supplier\BrandCollection;
use App\Http\Resources\LiveApi\V1\Supplier\BrandResource;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BrandCollectionTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id     = '55';
        $name   = 'a name';
        $images = [];
        $brand  = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->once()->andReturn($images);

        $resource = new BrandCollection(Collection::make([$brand]));

        $response = $resource->resolve();

        $data = [
            'data' => BrandResource::collection(Collection::make([$brand])),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BrandCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
