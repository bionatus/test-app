<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App\Http\Resources\LiveApi\V1\Order\ItemOrder\PartResource;
use App\Http\Resources\Models\Part\ImageResource;
use App\Http\Resources\Models\PartSpecificationResource;
use App\Models\AirFilter;
use App\Models\Item;
use App\Models\Other;
use App\Models\Part;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class PartResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

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
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn($number = '54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->twice()->andReturn($type = 'air_filter');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn($subtype = 'specific');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn($brand = 'a brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'https://image.com');
        $part->shouldReceive('getAttribute')->withArgs(['detail'])->once()->andReturn($airFilter);
        $part->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'fake subcategory');
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $resource = new PartResource($part);

        $response = $resource->resolve();

        $data = [
            'id'             => $id,
            'number'         => $number,
            'type'           => $type,
            'subtype'        => $subtype,
            'description'    => null,
            'brand'          => $brand,
            'image'          => new ImageResource($image),
            'subcategory'    => $subcategory,
            'specifications' => new PartSpecificationResource($part),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PartResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_other_type()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $sort = $this->faker->numberBetween();

        $other = Mockery::mock(Other::class);
        $other->shouldReceive('getAttribute')->withArgs(['sort'])->once()->andReturn($sort);
        $other->shouldReceive('getAttribute')
            ->withArgs(['description'])
            ->once()
            ->andReturn($description = 'fake description');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->withArgs(['number'])->once()->andReturn($number = '54-845Num');
        $part->shouldReceive('getAttribute')->withArgs(['type'])->twice()->andReturn($type = 'other');
        $part->shouldReceive('getAttribute')->withArgs(['subtype'])->once()->andReturn($subtype = 'specific');
        $part->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn($brand = 'a brand');
        $part->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'https://image.com');
        $part->shouldReceive('getAttribute')
            ->withArgs(['subcategory'])
            ->once()
            ->andReturn($subcategory = 'fake subcategory');
        $part->shouldReceive('getAttribute')->withArgs(['detail'])->times(3)->andReturn($other);
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnTrue();

        $resource = new PartResource($part);

        $response = $resource->resolve();

        $data = [
            'id'             => $id,
            'number'         => $number,
            'type'           => $type,
            'subtype'        => $subtype,
            'description'    => $description,
            'brand'          => $brand,
            'image'          => new ImageResource($image),
            'subcategory'    => $subcategory,
            'specifications' => new PartSpecificationResource($part),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PartResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
