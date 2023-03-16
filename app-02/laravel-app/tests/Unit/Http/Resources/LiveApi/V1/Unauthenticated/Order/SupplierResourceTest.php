<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\ImageResource;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\SupplierResource;
use App\Models\Media;
use App\Models\Supplier;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SupplierResourceTest extends TestCase
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
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'supplier uuid');
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturnNull();

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'   => $uuid,
            'logo' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_logo()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(false);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($uuid = 'supplier uuid');
        $supplier->shouldReceive('getFirstMedia')->with(MediaCollectionNames::LOGO)->once()->andReturn($media);

        $resource = new SupplierResource($supplier);
        $response = $resource->resolve();
        $data     = [
            'id'   => $uuid,
            'logo' => new ImageResource($media),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplierResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
