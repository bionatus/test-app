<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\CompressorDetailsResource;
use App\Http\Resources\LiveApi\V1\Oem\ConversionJobsResource;
use App\Http\Resources\LiveApi\V1\Oem\DetailedResource;
use App\Http\Resources\LiveApi\V1\Oem\ManualsResource;
use App\Http\Resources\LiveApi\V1\Oem\MeteringDeviceDetailsResource;
use App\Http\Resources\LiveApi\V1\Oem\OilDetailsResource;
use App\Http\Resources\LiveApi\V1\Oem\RefrigerantDetailsResource;
use App\Http\Resources\LiveApi\V1\Oem\SeriesResource;
use App\Http\Resources\LiveApi\V1\Oem\SystemDetailsResource;
use App\Http\Resources\LiveApi\V1\Oem\TagCollection;
use App\Http\Resources\LiveApi\V1\Oem\WarningCollection;
use App\Models\ConversionJob;
use App\Models\Oem;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        ConversionJob::factory()->create(['control' => 'standard']);
        ConversionJob::factory()->create(['control' => 'optional']);
        Warning::factory()->create(['title' => 'warning']);

        $oem          = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM => 'https://server.domain/file.pdf',
            'model_type_id'      => null,
            'standard_controls'  => 'standard',
            'optional_controls'  => 'optional',
            'warnings'           => 'warning',
        ]);
        $manualsCount = 1;

        $tags = new LengthAwarePaginator([], 0, 15, null, [
            'path'     => 'http://localhost',
            'pageName' => 'page',
        ]);

        $resource = new DetailedResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'                      => $oem->getRouteKey(),
            'status'                  => $oem->status,
            'model'                   => $oem->model,
            'model_description'       => $oem->model_description,
            'model_notes'             => $oem->model_notes,
            'logo'                    => $oem->logo,
            'image'                   => $oem->unit_image,
            'call_group_tags'         => $oem->call_group_tags,
            'calling_groups'          => $oem->calling_groups,
            'series'                  => new SeriesResource($oem->series),
            'system_details'          => new SystemDetailsResource($oem),
            'refrigerant_details'     => new RefrigerantDetailsResource($oem),
            'compressor_details'      => new CompressorDetailsResource($oem),
            'oil_details'             => new OilDetailsResource($oem),
            'metering_device_details' => new MeteringDeviceDetailsResource($oem),
            'tags'                    => new TagCollection($tags),
            'manuals'                 => new ManualsResource($oem),
            'conversions'             => new ConversionJobsResource($oem),
            'warnings'                => new WarningCollection(Warning::get()),
            'functional_parts_count'  => $oem->functionalPartsCount(),
            'posts_count'             => $oem->postsCount(),
            'manuals_count'           => $manualsCount,
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
