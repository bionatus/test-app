<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\PartResource;
use App\Http\Resources\Models\RecommendedReplacementResource;
use App\Http\Resources\Models\SupplierResource;
use App\Models\Item;
use App\Models\Part;
use App\Models\RecommendedReplacement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RecommendedReplacementResourceTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_has_correct_fields()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('supplier key');
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('supplier name');
        $supplier->shouldReceive('getAttribute')->with('address')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('latitude')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('longitude')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $supplier->shouldReceive('getAttribute')->with('published_at')->once()->andReturnNull();
        $supplier->shouldReceive('isCurriDeliveryEnabled')->withNoArgs()->once()->andReturnTrue();

        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn('54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn('https://image.com');
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturnNull();
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $recommendedReplacement = Mockery::mock(RecommendedReplacement::class);
        $recommendedReplacement->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $recommendedReplacement->shouldReceive('getAttribute')->with('supplier')->twice()->andReturn($supplier);
        $recommendedReplacement->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);
        $recommendedReplacement->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand = 'brand');
        $recommendedReplacement->shouldReceive('getAttribute')
            ->with('part_number')
            ->once()
            ->andReturn($partNumber = 'part number');
        $recommendedReplacement->shouldReceive('getAttribute')->with('note')->once()->andReturn($note = 'note');

        $resource = new RecommendedReplacementResource($recommendedReplacement);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'supplier'      => new SupplierResource($supplier),
            'original_part' => new PartResource($part),
            'brand'         => $brand,
            'part_number'   => $partNumber,
            'note'          => $note,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(RecommendedReplacementResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn($number = '54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'https://image.com');
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturnNull();
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $recommendedReplacement = Mockery::mock(RecommendedReplacement::class);
        $recommendedReplacement->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $recommendedReplacement->shouldReceive('getAttribute')->with('supplier')->once()->andReturnNull();
        $recommendedReplacement->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);
        $recommendedReplacement->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand = 'brand');
        $recommendedReplacement->shouldReceive('getAttribute')
            ->with('part_number')
            ->once()
            ->andReturn($partNumber = 'part number');
        $recommendedReplacement->shouldReceive('getAttribute')->with('note')->once()->andReturnNull();

        $resource = new RecommendedReplacementResource($recommendedReplacement);

        $response = $resource->resolve();

        $data = [
            'id'            => $id,
            'supplier'      => null,
            'original_part' => new PartResource($part),
            'brand'         => $brand,
            'part_number'   => $partNumber,
            'note'          => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(RecommendedReplacementResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
