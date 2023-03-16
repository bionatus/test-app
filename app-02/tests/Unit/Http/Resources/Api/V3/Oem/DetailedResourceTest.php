<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\ConversionJobsResource;
use App\Http\Resources\Api\V3\Oem\DetailedResource;
use App\Http\Resources\Api\V3\Oem\ManualsResource;
use App\Http\Resources\Api\V3\Oem\SeriesResource;
use App\Http\Resources\Api\V3\Oem\TagCollection;
use App\Http\Resources\Api\V3\Oem\WarningCollection;
use App\Models\ConversionJob;
use App\Models\Layout;
use App\Models\Oem;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $layout = Mockery::mock(Layout::class);
        $layout->shouldReceive('jsonSerialize')->withNoArgs()->once()->andReturn(['layout' => true]);

        ConversionJob::factory()->create(['control' => 'standard']);
        ConversionJob::factory()->create(['control' => 'optional']);
        Warning::factory()->create(['title' => 'warning']);

        $oem = Oem::factory()->create([
            Oem::MANUAL_TYPE_IOM => 'https://server.domain/file.pdf',
            'model_type_id'      => null,
            'standard_controls'  => 'standard',
            'optional_controls'  => 'optional',
            'warnings'           => 'warning',
        ]);

        $tags = new LengthAwarePaginator([], 0, 15, null, [
            'path'     => 'http://localhost',
            'pageName' => 'page',
        ]);

        /** @var User $user */
        $user = User::factory()->create();

        $resource = new DetailedResource($oem, $layout, $user);

        $response = $resource->resolve();

        $data = [
            'id'                      => $oem->getRouteKey(),
            'status'                  => $oem->status,
            'series'                  => new SeriesResource($oem->series),
            'model'                   => $oem->model,
            'logo'                    => $oem->logo,
            'image'                   => $oem->unit_image,
            'call_group_tags'         => $oem->call_group_tags,
            'calling_groups'          => $oem->calling_groups,
            'type'                    => $oem->modelType ? $oem->modelType->name : null,
            'model_description'       => $oem->model_description,
            'model_notes'             => $oem->model_notes,
            'tonnage'                 => $oem->tonnage,
            'total_circuits'          => $oem->total_circuits,
            'dx_chiller'              => $oem->dx_chiller,
            'cooling_type'            => $oem->cooling_type,
            'heating_type'            => $oem->heating_type,
            'seer'                    => $oem->seer,
            'eer'                     => $oem->eer,
            'refrigerant'             => $oem->refrigerant,
            'original_charge_oz'      => $oem->original_charge_oz,
            'compressor_brand'        => $oem->compressor_brand,
            'compressor_type'         => $oem->compressor_type,
            'compressor_model'        => $oem->compressor_model,
            'total_compressors'       => $oem->total_compressors,
            'compressors_per_circuit' => $oem->compressors_per_circuit,
            'compressor_sizes'        => $oem->compressor_sizes,
            'rla'                     => $oem->rla,
            'lra'                     => $oem->lra,
            'capacity_staging'        => $oem->capacity_staging,
            'lowest_staging'          => $oem->lowest_staging,
            'compressor_notes'        => $oem->compressor_notes,
            'oil_type'                => $oem->oil_type,
            'device_type'             => $oem->device_type,
            'devices_per_circuit'     => $oem->devices_per_circuit,
            'total_devices'           => $oem->total_devices,
            'device_size'             => $oem->device_size,
            'metering_device_notes'   => $oem->metering_device_notes,
            'fan_type'                => $oem->fan_type,
            'cfm_range'               => $oem->cfm_range,
            'fan_notes'               => $oem->fan_notes,
            'voltage_phase_hz'        => $oem->voltage_phase_hz,
            'conversion_job'          => $oem->conversion_job,
            'bid_type'                => $oem->bid_type,
            'man_hours'               => $oem->man_hours,
            'charge_lbs'              => $oem->charge_lbs,
            'exv'                     => $oem->exv,
            'exv_qty'                 => $oem->exv_qty,
            'exv_2'                   => $oem->exv_2,
            'exv_2_qty'               => $oem->exv_2_qty,
            'control_panel'           => $oem->control_panel,
            'inspect'                 => $oem->inspect,
            'baseline'                => $oem->baseline,
            'airflow_waterflow'       => $oem->airflow_waterflow,
            'recover'                 => $oem->recover,
            'controls'                => $oem->controls,
            'replace_install'         => $oem->replace_install,
            'leak_check'              => $oem->leak_check,
            'evacuate'                => $oem->evacuate,
            'charge'                  => $oem->charge,
            'tune'                    => $oem->tune,
            'verify'                  => $oem->verify,
            'label'                   => $oem->label,
            'conversion_notes'        => $oem->conversion_notes,
            'date_added'              => $oem->date_added,
            'qa'                      => $oem->qa,
            'qa_qc_comments'          => $oem->qa_qc_comments,
            'last_qc_date'            => $oem->last_qc_date,
            'info_source'             => $oem->info_source,
            'source'                  => $oem->source,
            'match'                   => $oem->match,
            'syncing_notes'           => $oem->syncing_notes,
            'layout'                  => $layout,
            'tags'                    => new TagCollection($tags),
            'manuals'                 => new ManualsResource($oem),
            'conversions'             => new ConversionJobsResource($oem),
            'warnings'                => new WarningCollection(Warning::paginate()),
            'functional_parts_count'  => $oem->functionalPartsCount(),
            'posts_count'             => $oem->postsCount(),
            'favorite'                => false,
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
