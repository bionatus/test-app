<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\ManualResource;
use App\Http\Resources\LiveApi\V1\Oem\ManualsResource;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualsResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $oem = Oem::factory()->create([
            Oem::MANUAL_TYPE_GUIDELINES       => 'https://guidelines.pdf',
            Oem::MANUAL_TYPE_DIAGNOSTIC       => 'https://diagnostic.pdf',
            Oem::MANUAL_TYPE_IOM              => 'https://iom.pdf',
            Oem::MANUAL_TYPE_MISCELLANEOUS    => 'https://miscellaneous.pdf',
            Oem::MANUAL_TYPE_PRODUCT_DATA     => 'https://product_data.pdf',
            Oem::MANUAL_TYPE_SERVICE_FACTS    => 'https://service_facts.pdf',
            Oem::MANUAL_TYPE_WIRING_DIAGRAM   => 'https://wiring_diagram.pdf',
            Oem::MANUAL_TYPE_CONTROLS_MANUALS => 'https://controls_manuals.pdf',
        ]);

        $resource = new ManualsResource($oem);

        $response = $resource->resolve();

        $data = [
            'bluon_guidelines' => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_GUIDELINES)),
            'diagnostic'       => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_DIAGNOSTIC)),
            'iom'              => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_IOM)),
            'misc'             => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_MISCELLANEOUS)),
            'product_data'     => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_PRODUCT_DATA)),
            'service_facts'    => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_SERVICE_FACTS)),
            'wiring_diagram'   => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_WIRING_DIAGRAM)),
            'controls_manuals' => ManualResource::collection($oem->manualType(Oem::MANUAL_TYPE_CONTROLS_MANUALS)),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ManualsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
