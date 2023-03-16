<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\PartResource;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\Item;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use Mockery;
use Tests\TestCase;

class ReplacementResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_single_replacement()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn('54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn('air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn('specific');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn('a brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn('https://image.com');
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturn('fake subcategory');
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $singleReplacement = Mockery::mock(SingleReplacement::class);
        $singleReplacement->shouldReceive('getAttribute')->with('part')->once()->andReturn($part);

        $replacement = Mockery::mock(Replacement::class);
        $replacement->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($id = '464aac0c-aa5c-40f0-9f9c-936ddcaedc24');
        $replacement->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = Replacement::TYPE_SINGLE);
        $replacement->shouldReceive('getAttribute')->with('singleReplacement')->once()->andReturn($singleReplacement);
        $replacement->shouldReceive('completeNotes')->withNoArgs()->once()->andReturn($note = 'Fake note');
        $replacement->shouldReceive('isSingle')->withNoArgs()->once()->andReturnTrue();

        $resource = new ReplacementResource($replacement);

        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'type'    => $type,
            'note'    => $note,
            'details' => new PartResource($part),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ReplacementResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_grouped_replacement()
    {
        $replacement = Mockery::mock(Replacement::class);
        $replacement->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($id = '464aac0c-aa5c-40f0-9f9c-936ddcaedc24');
        $replacement->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = Replacement::TYPE_SINGLE);
        $replacement->shouldReceive('completeNotes')->withNoArgs()->once()->andReturnNull();
        $replacement->shouldReceive('isSingle')->withNoArgs()->once()->andReturnFalse();

        $resource = new ReplacementResource($replacement);

        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'type'    => $type,
            'note'    => null,
            'details' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ReplacementResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
