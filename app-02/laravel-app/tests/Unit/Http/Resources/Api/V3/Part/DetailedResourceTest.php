<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\DetailedResource;
use App\Http\Resources\Models\Part\ImageResource;
use App\Http\Resources\Models\PartSpecificationResource;
use App\Models\AirFilter;
use App\Models\Other;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\Tip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $subtype     = $this->faker->text();
        $brand       = $this->faker->text();
        $image       = $this->faker->text();
        $subcategory = $this->faker->text();

        $tip = Tip::factory()->create(['description' => $tipDescription = 'A tip']);

        $part = Part::factory()->usingTip($tip)->create([
            'subtype'     => $subtype,
            'brand'       => $brand,
            'image'       => $image,
            'subcategory' => $subcategory,
        ]);
        AirFilter::factory()->usingPart($part)->create();

        $replacements = Replacement::factory()->usingPart($part)->count(3)->create();

        $resource = new DetailedResource($part);

        $response = $resource->resolve();

        $data = [
            'id'                 => $part->item->uuid,
            'number'             => $part->hiddenNumber(),
            'type'               => $part->type,
            'subtype'            => $subtype,
            'description'        => null,
            'brand'              => $brand,
            'image'              => new ImageResource($image),
            'subcategory'        => $subcategory,
            'tip'                => $tipDescription,
            'replacements_count' => $replacements->count(),
            'specifications'     => new PartSpecificationResource($part),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_other_type()
    {
        $subtype     = $this->faker->text();
        $brand       = $this->faker->text();
        $image       = $this->faker->text();
        $description = $this->faker->text(20);
        $subcategory = $this->faker->text();

        $tip = Tip::factory()->create(['description' => $tipDescription = 'A tip']);

        $part = Part::factory()->usingTip($tip)->create([
            'subtype'     => $subtype,
            'brand'       => $brand,
            'image'       => $image,
            'type'        => Part::TYPE_OTHER,
            'subcategory' => $subcategory,
        ]);
        Other::factory()->usingPart($part)->create(['description' => $description]);

        $replacements = Replacement::factory()->usingPart($part)->count(3)->create();

        $resource = new DetailedResource($part);

        $response = $resource->resolve();

        $data = [
            'id'                 => $part->item->uuid,
            'number'             => $part->hiddenNumber(),
            'type'               => $part->type,
            'subtype'            => $subtype,
            'description'        => $description,
            'brand'              => $brand,
            'image'              => new ImageResource($image),
            'subcategory'        => $subcategory,
            'tip'                => $tipDescription,
            'replacements_count' => $replacements->count(),
            'specifications'     => new PartSpecificationResource($part),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
