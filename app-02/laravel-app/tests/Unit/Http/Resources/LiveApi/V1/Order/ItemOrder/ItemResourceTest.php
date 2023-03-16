<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App\Http\Resources\LiveApi\V1\Order\ItemOrder\ItemResource;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\PartResource;
use App\Models\AirFilter;
use App\Models\Item;
use App\Models\Part;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields_with_part()
    {
        $mediaType        = $this->faker->text(50);
        $mervRating       = $this->faker->numberBetween();
        $nominalWidth     = $this->faker->text(10);
        $nominalLength    = $this->faker->text(10);
        $nominalDepth     = $this->faker->text(10);
        $actualWidth      = $this->faker->text(10);
        $actualLength     = $this->faker->text(10);
        $actualDepth      = $this->faker->text(10);
        $efficiency       = $this->faker->text(10);
        $maxOperatingTemp = $this->faker->text(10);

        $airFilter = Mockery::mock(AirFilter::class);
        $airFilter->shouldReceive('getAttribute')->withArgs(['media_type'])->once()->andReturn($mediaType);
        $airFilter->shouldReceive('getAttribute')->withArgs(['merv_rating'])->once()->andReturn($mervRating);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_width'])->once()->andReturn($nominalWidth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_length'])->once()->andReturn($nominalLength);
        $airFilter->shouldReceive('getAttribute')->withArgs(['nominal_depth'])->once()->andReturn($nominalDepth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_width'])->once()->andReturn($actualWidth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_length'])->once()->andReturn($actualLength);
        $airFilter->shouldReceive('getAttribute')->withArgs(['actual_depth'])->once()->andReturn($actualDepth);
        $airFilter->shouldReceive('getAttribute')->withArgs(['efficiency'])->once()->andReturn($efficiency);
        $airFilter->shouldReceive('getAttribute')
            ->withArgs(['max_operating_temp'])
            ->once()
            ->andReturn($maxOperatingTemp);

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn('ABC123');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->twice()->andReturn('air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn('fake subtype');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn('fake brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturnNull();
        $part->shouldReceive('getAttribute')->withArgs(['subcategory'])->once()->andReturn('Air Filter');
        $part->shouldReceive('getAttribute')->withArgs(['detail'])->once()->andReturn($airFilter);
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->twice()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'part');
        $item->shouldReceive('getAttribute')->withArgs(['part'])->once()->andReturn($part);
        $item->shouldReceive('isPart')->withNoArgs()->twice()->andReturnTrue();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnFalse();

        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $item->shouldReceive('getAttribute')->withArgs(['orderable'])->once()->andReturn($part);

        $resource = new ItemResource($item);

        $response = $resource->resolve();

        $data = [
            'id'   => $id,
            'type' => $type,
            'info' => new PartResource($part),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
