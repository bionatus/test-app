<?php

namespace Tests\Unit\Http\Resources\Api\V4\Supplier\DefaultSupplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V4\Supplier\DefaultSupplier\BaseResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('getAttribute')->with('uuid')->twice()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturn(false);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address'])
            ->once()
            ->andReturn($address = 'Adress lorem ipsum');
        $supplier->shouldReceive('getAttribute')
            ->withArgs(['address_2'])
            ->once()
            ->andReturn($addressTwo = 'Second Adress lorem ipsum');
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturn($city = 'City lorem ipsum');
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturn($media);
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->andReturnTrue();

        $resource = new BaseResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => $address,
            'address_2'              => $addressTwo,
            'city'                   => $city,
            'logo'                   => new ImageResource($media),
            'image'                  => new ImageResource($media),
            'can_use_curri_delivery' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '1234-56789');
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'A supplier');
        $supplier->shouldReceive('getAttribute')->withArgs(['address'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['address_2'])->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->withArgs(['city'])->once()->andReturnNull();
        $supplier->shouldReceive('getFirstMedia')->withAnyArgs()->twice()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->andReturnFalse();

        $resource = new BaseResource($supplier);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'name'                   => $name,
            'address'                => null,
            'address_2'              => null,
            'city'                   => null,
            'logo'                   => null,
            'image'                  => null,
            'can_use_curri_delivery' => false,

        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
