<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order\ItemOrder\Part;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\Part\BaseResource;
use App\Http\Resources\Models\PartResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Other;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $temOrderId = $this->faker->uuid;
        $itemId     = $this->faker->uuid;
        $status     = ItemOrder::STATUS_PENDING;

        $other = Mockery::mock(Other::class);
        $other->shouldReceive('getAttribute')->with('description')->once()->andReturn('fake description');

        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($itemId);

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('number')->once()->andReturn('number');
        $part->shouldReceive('getAttribute')->with('type')->once()->andReturn('type');
        $part->shouldReceive('getAttribute')->with('subtype')->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('detail')->twice()->andReturn($other);
        $part->shouldReceive('getAttribute')->with('brand')->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('subcategory')->once()->andReturnNull();
        $part->shouldReceive('isOther')->withAnyArgs()->once()->andReturnTrue();

        $item->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);
        $itemOrder = Mockery::mock(ItemOrder::class);

        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($temOrderId);
        $itemOrder->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $itemOrder->shouldReceive('getAttribute')->with('replacement')->once()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')->with('generic_part_description')->twice()->andReturnNull();
        $itemOrder->shouldReceive('getAttribute')
            ->with('supply_detail')
            ->once()
            ->andReturn($supplyDetail = 'supply_detail');
        $itemOrder->shouldReceive('getAttribute')
            ->with('custom_detail')
            ->once()
            ->andReturn($customDetail = 'custom_detail');
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price = 100);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = 2);
        $itemOrder->shouldReceive('getAttribute')
            ->with('quantity_requested')
            ->once()
            ->andReturn($quantityRequested = 3);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $temOrderId,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => $supplyDetail,
            'custom_detail'            => $customDetail,
            'generic_part_description' => null,
            'item'                     => new PartResource($part),
            'replacement'              => null,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_replacement()
    {
        $temOrderId    = $this->faker->uuid;
        $itemId        = $this->faker->uuid;
        $replacementId = $this->faker->uuid;
        $status        = ItemOrder::STATUS_PENDING;

        $other = Mockery::mock(Other::class);
        $other->shouldReceive('getAttribute')->with('description')->twice()->andReturn('fake description');

        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->twice()->andReturn($itemId);

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('item')->twice()->andReturn($item);
        $part->shouldReceive('getAttribute')->with('image')->twice()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('number')->twice()->andReturn('number');
        $part->shouldReceive('getAttribute')->with('type')->twice()->andReturn('type');
        $part->shouldReceive('getAttribute')->with('subtype')->twice()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('detail')->times(4)->andReturn($other);
        $part->shouldReceive('getAttribute')->with('brand')->twice()->andReturnNull();
        $part->shouldReceive('getAttribute')->with('subcategory')->twice()->andReturnNull();
        $part->shouldReceive('isOther')->withAnyArgs()->twice()->andReturnTrue();

        $item->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);
        $itemOrder = Mockery::mock(ItemOrder::class);

        $singleReplacement = Mockery::mock(SingleReplacement::class);
        $singleReplacement->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);

        $replacement = Mockery::mock(Replacement::class);
        $replacement->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($replacementId);

        $replacement->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = Replacement::TYPE_SINGLE);
        $replacement->shouldReceive('getAttribute')->with('singleReplacement')->once()->andReturn($singleReplacement);
        $replacement->shouldReceive('completeNotes')->withNoArgs()->once()->andReturn($note = 'Fake note');
        $replacement->shouldReceive('isSingle')->withNoArgs()->once()->andReturnTrue();

        $itemOrder->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($temOrderId);
        $itemOrder->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $itemOrder->shouldReceive('getAttribute')->with('replacement')->twice()->andReturn($replacement);
        $itemOrder->shouldReceive('getAttribute')
            ->with('generic_part_description')
            ->twice()
            ->andReturn($genericPartDescription = 'generic_part_description');
        $itemOrder->shouldReceive('getAttribute')
            ->with('supply_detail')
            ->once()
            ->andReturn($supplyDetail = 'supply_detail');
        $itemOrder->shouldReceive('getAttribute')
            ->with('custom_detail')
            ->once()
            ->andReturn($customDetail = 'custom_detail');
        $itemOrder->shouldReceive('getAttribute')->with('price')->once()->andReturn($price = 100);
        $itemOrder->shouldReceive('getAttribute')->with('quantity')->once()->andReturn($quantity = 2);
        $itemOrder->shouldReceive('getAttribute')
            ->with('quantity_requested')
            ->once()
            ->andReturn($quantityRequested = 3);
        $itemOrder->shouldReceive('getAttribute')->with('status')->once()->andReturn($status);
        $itemOrder->shouldReceive('getAttribute')->with('initial_request')->once()->andReturnTrue();

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $temOrderId,
            'quantity'                 => $quantity,
            'quantity_requested'       => $quantityRequested,
            'price'                    => $price,
            'status'                   => $status,
            'supply_detail'            => $supplyDetail,
            'custom_detail'            => $customDetail,
            'generic_part_description' => $genericPartDescription,
            'item'                     => new PartResource($part),
            'replacement'              => new ReplacementResource($itemOrder->replacement),
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
