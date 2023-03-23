<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Supplier;

use App\Http\Resources\Api\V3\Account\Supplier\BriefResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class BriefResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(false);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address'])
            ->once()
            ->andReturn($address = 'Address lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['published_at'])->once()->andReturn(new Carbon());
        $supplier->shouldReceive('getAttribute')->withArgs(['verified_at'])->once()->andReturn(new Carbon());
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturn($media);

        $resource = new BriefResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                  => $id,
            'name'                => $name,
            'address'             => $address,
            'logo'                => new ImageResource($media),
            'image'               => new ImageResource($media),
            'preferred'           => false,
            'bluon_live_verified' => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BriefResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['preferred_supplier'])->once()->andReturnFalse();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['verified_at'])->once()->andReturnNull();

        $resource = new BriefResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                  => $id,
            'name'                => $name,
            'address'             => null,
            'logo'                => null,
            'image'               => null,
            'preferred'           => false,
            'bluon_live_verified' => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BriefResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
