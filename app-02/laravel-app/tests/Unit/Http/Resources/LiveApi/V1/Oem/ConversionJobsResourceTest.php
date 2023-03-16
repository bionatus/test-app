<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\ConversionJobCollection;
use App\Http\Resources\LiveApi\V1\Oem\ConversionJobsResource;
use App\Models\ConversionJob;
use App\Models\ConversionJob\Scopes\ByControls;
use App\Models\Oem;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionJobsResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $standard = ConversionJob::factory()->count(2)->sequence(function(Sequence $sequence) {
            return ['control' => "standard {$sequence->index}"];
        })->create();

        $optional = ConversionJob::factory()->count(3)->sequence(function(Sequence $sequence) {
            return ['control' => "optional {$sequence->index}"];
        })->create();

        $standardControls = $standard->pluck('control');
        $optionalControls = $optional->pluck('control');

        $standardPage = ConversionJob::query()->scoped(new ByControls($standardControls->toArray()))->get();
        $optionalPage = ConversionJob::query()->scoped(new ByControls($optionalControls->toArray()))->get();

        $oem = Oem::factory()->create([
            'standard_controls' => $standardControls->implode(','),
            'optional_controls' => $optionalControls->implode(','),
        ]);

        $resource = new ConversionJobsResource($oem);

        $response = $resource->resolve();

        $data = [
            'standard_controls' => new ConversionJobCollection($standardPage),
            'optional_controls' => new ConversionJobCollection($optionalPage),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ConversionJobsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
