<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order\ItemOrder\Replacement;

use App\Http\Resources\LiveApi\V2\Order\ItemOrder\Replacement\BaseResource;
use App\Http\Resources\Models\PartResource;
use App\Models\Item;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields_when_replacement_type_is_single()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('item-id');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn('54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn('Fake brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn('fake-image.jpeg');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn('specific');
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturn('fake subcategory');
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $singleReplacement = Mockery::mock(SingleReplacement::class);
        $singleReplacement->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);

        $replacement = Mockery::mock(Replacement::class);
        $replacement->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'replacement-id');
        $replacement->shouldReceive('getAttribute')
            ->withArgs(['type'])
            ->once()
            ->andReturn($type = Replacement::TYPE_SINGLE);
        $replacement->shouldReceive('getAttribute')->with('singleReplacement')->once()->andReturn($singleReplacement);
        $replacement->shouldReceive('isSingle')->once()->andReturnTrue();
        $replacement->shouldReceive('completeNotes')
            ->once()
            ->andReturn($completeNotes = 'Replacement fake note.\nPart fake note');

        $resource = new BaseResource($replacement);
        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'type'    => $type,
            'note'    => $completeNotes,
            'details' => new PartResource($part),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_when_replacement_type_is_grouped()
    {
        $replacement = Mockery::mock(Replacement::class);
        $replacement->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'replacement-id');
        $replacement->shouldReceive('getAttribute')
            ->withArgs(['type'])
            ->once()
            ->andReturn($type = Replacement::TYPE_GROUPED);
        $replacement->shouldReceive('isSingle')->once()->andReturnFalse();
        $replacement->shouldReceive('completeNotes')->once()->andReturnNull();

        $resource = new BaseResource($replacement);
        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'type'    => $type,
            'note'    => null,
            'details' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
