<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\ItemResource;
use App\Http\Resources\Models\PartResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Media;
use App\Models\Part;
use App\Models\Supply;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ItemResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields_with_part(bool $hideNumber)
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->twice()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'part');
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnFalse();

        $part = Mockery::mock(Part::class);
        if ($hideNumber) {
            $part->shouldReceive('hiddenNumber')->withNoArgs()->once()->andReturn('hidden number');
        } else {
            $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn('ABC123');
        }
        $part->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn('fake subtype');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn('fake brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturn('Air Filter');
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $item->shouldReceive('getAttribute')->withArgs(['orderable'])->once()->andReturn($part);

        $resource = new ItemResource($item, $hideNumber);

        $response = $resource->resolve();

        $data = [
            'id'   => $id,
            'type' => $type,
            'info' => new PartResource($part, $hideNumber),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields_with_supply(bool $hideNumber)
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->twice()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'supply');
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnFalse();

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnFalse();

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('Red Hook Term Connectors');
        $supply->shouldReceive('getAttribute')
            ->withArgs(['internal_name'])
            ->once()
            ->andReturn('Red Hook Term Connectors');
        $supply->shouldReceive('getAttribute')->withArgs(['sort'])->once()->andReturn(1);
        $supply->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturn($media);

        $item->shouldReceive('getAttribute')->withArgs(['orderable'])->once()->andReturn($supply);

        $resource = new ItemResource($item, $hideNumber);

        $response = $resource->resolve();

        $data = [
            'id'   => $id,
            'type' => $type,
            'info' => new SupplyResource($supply),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields_with_custom_item(bool $hideNumber)
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->twice()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'custom_item');
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnTrue();

        $customItem = Mockery::mock(CustomItem::class);
        $customItem->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('Custom Item');
        $customItem->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->withArgs(['creator_type'])->once()->andReturn('Creator Type');

        $item->shouldReceive('getAttribute')->withArgs(['orderable'])->once()->andReturn($customItem);

        $resource = new ItemResource($item, $hideNumber);

        $response = $resource->resolve();

        $data = [
            'id'   => $id,
            'type' => $type,
            'info' => new CustomItemResource($customItem),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function dataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
