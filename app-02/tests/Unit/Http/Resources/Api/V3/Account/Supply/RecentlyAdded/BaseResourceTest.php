<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Supply\RecentlyAdded;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\Account\Supply\RecentlyAdded\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplyCategoryResource;
use App\Models\Item;
use App\Models\Media;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $supplyCategory = Mockery::mock(SupplyCategory::class);
        $supplyCategory->shouldReceive('getRouteKey')->withNoArgs()->andReturn('fake-supply-category');
        $supplyCategory->shouldReceive('getAttribute')->with('name')->once()->andReturn('Cable');
        $supplyCategory->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturnNull();

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supply->shouldReceive('getAttribute')
            ->with('internal_name')
            ->once()
            ->andReturn($internalName = 'internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturnNull();
        $supply->shouldReceive('getAttribute')->with('supplyCategory')->once()->andReturn($supplyCategory);
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturnNull();

        $response = (new BaseResource($supply))->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'internal_name' => $internalName,
            'image'         => null,
            'category'      => new SupplyCategoryResource($supplyCategory),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->with('uuid')->twice()->andReturn('media uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->twice()->andReturn('media url');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->twice()->andReturnFalse();

        $supplyCategory = Mockery::mock(SupplyCategory::class);
        $supplyCategory->shouldReceive('getRouteKey')->withNoArgs()->andReturn('fake-supply-category');
        $supplyCategory->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $supplyCategory->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('Cable');

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supply->shouldReceive('getAttribute')
            ->withArgs(['internal_name'])
            ->once()
            ->andReturn($internalName = 'internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturn(1);
        $supply->shouldReceive('getAttribute')->with('supplyCategory')->once()->andReturn($supplyCategory);
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturn($media);

        $response = (new BaseResource($supply))->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'internal_name' => $internalName,
            'image'         => new ImageResource($media),
            'category'      => new SupplyCategoryResource($supplyCategory),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
