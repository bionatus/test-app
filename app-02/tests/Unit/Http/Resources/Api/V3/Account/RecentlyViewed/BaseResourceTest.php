<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\RecentlyViewed;

use App\Http\Resources\Api\V3\Account\RecentlyViewed\BaseResource;
use App\Http\Resources\Models\OemResource;
use App\Http\Resources\Models\PartResource;
use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\User;
use App\Types\RecentlyViewed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws \Exception
     */
    public function it_has_correct_fields_for_oem()
    {
        $user         = User::factory()->create();
        $objectViewed = OemDetailCounter::factory()->usingUser($user)->create();

        $rawViewed = new RecentlyViewed([
            'object_id'   => $objectViewed->oem_id,
            'object_type' => Oem::MORPH_ALIAS,
            'object'      => $objectViewed->oem,
            'viewed_at'   => $objectViewed->created_at,
        ]);

        $resource = new BaseResource($rawViewed);

        $response = $resource->resolve();

        $data = [
            'type' => $rawViewed->object_type,
            'info' => new OemResource($rawViewed->object),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @throws \Exception
     */
    public function it_has_correct_fields_for_part()
    {
        $user         = User::factory()->create();
        $objectViewed = PartDetailCounter::factory()->usingUser($user)->create();

        $rawViewed = new RecentlyViewed([
            'object_id'   => $objectViewed->part_id,
            'object_type' => Part::MORPH_ALIAS,
            'object'      => $objectViewed->part,
            'viewed_at'   => $objectViewed->created_at,
        ]);

        $resource = new BaseResource($rawViewed);

        $response = $resource->resolve();

        $data = [
            'type' => $rawViewed->object_type,
            'info' => new PartResource($rawViewed->object),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
