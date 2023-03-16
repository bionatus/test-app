<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\ItemResource;
use App\Http\Resources\Models\GenericReplacementResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\AirFilter;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Part;
use App\Models\SingleReplacement;
use App\Models\Supply;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_with_part()
    {
        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingItem($part->item)->createQuietly();

        $user = Mockery::mock(Supplier::class);
        $user->shouldReceive('can')->withAnyArgs()->once()->andReturnFalse();

        $auth = Auth::shouldReceive('user')->withAnyArgs()->once()->andReturn($user);
        App::bind(Auth::class, fn() => $auth);

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($part->item),
            'replacement'              => null,
            'authorized_to_delete'     => false,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_part_and_replacement()
    {
        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $singleReplacement = SingleReplacement::factory()->usingPart($part)->create();
        $itemOrder         = ItemOrder::factory()
            ->usingItem($part->item)
            ->usingReplacement($singleReplacement->replacement)
            ->createQuietly();

        $user = Mockery::mock(Supplier::class);
        $user->shouldReceive('can')->withAnyArgs()->once()->andReturnFalse();

        $auth = Auth::shouldReceive('user')->withAnyArgs()->once()->andReturn($user);
        App::bind(Auth::class, fn() => $auth);

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($part->item),
            'replacement'              => new ReplacementResource($itemOrder->replacement),
            'authorized_to_delete'     => false,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_part_and_generic_replacement()
    {
        $part = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()
            ->usingItem($part->item)
            ->createQuietly(['generic_part_description' => 'a part description']);

        $user = Mockery::mock(Supplier::class);
        $user->shouldReceive('can')->withAnyArgs()->once()->andReturnFalse();

        $auth = Auth::shouldReceive('user')->withAnyArgs()->once()->andReturn($user);
        App::bind(Auth::class, fn() => $auth);

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($part->item),
            'replacement'              => new GenericReplacementResource($itemOrder->generic_part_description),
            'authorized_to_delete'     => false,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $supply    = Supply::factory()->create();
        $itemOrder = ItemOrder::factory()->usingItem($supply->item)->createQuietly(['supply_detail' => 'detail test']);

        $user = Mockery::mock(Supplier::class);
        $user->shouldReceive('can')->withAnyArgs()->once()->andReturnFalse();

        $auth = Auth::shouldReceive('user')->withAnyArgs()->once()->andReturn($user);
        App::bind(Auth::class, fn() => $auth);

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($supply->item),
            'replacement'              => null,
            'authorized_to_delete'     => false,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(false), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_item()
    {
        $customItem = CustomItem::factory()->create();
        $itemOrder  = ItemOrder::factory()
            ->usingItem($customItem->item)
            ->createQuietly(['custom_detail' => 'detail test']);

        $user = Mockery::mock(Supplier::class);
        $user->shouldReceive('can')->withAnyArgs()->once()->andReturnFalse();

        $auth = Auth::shouldReceive('user')->withAnyArgs()->once()->andReturn($user);
        App::bind(Auth::class, fn() => $auth);

        $resource = new BaseResource($itemOrder);
        $response = $resource->resolve();

        $data = [
            'id'                       => $itemOrder->getRouteKey(),
            'quantity'                 => $itemOrder->quantity,
            'quantity_requested'       => $itemOrder->quantity_requested,
            'price'                    => $itemOrder->price,
            'status'                   => $itemOrder->status,
            'supply_detail'            => $itemOrder->supply_detail,
            'custom_detail'            => $itemOrder->custom_detail,
            'generic_part_description' => $itemOrder->generic_part_description,
            'item'                     => new ItemResource($customItem->item),
            'replacement'              => null,
            'authorized_to_delete'     => false,
            'initial_request'          => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(false), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
