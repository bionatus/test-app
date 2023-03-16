<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Oem\RecentlyViewed;

use App\Http\Resources\Api\V3\Account\Oem\RecentlyViewed\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        Carbon::setTestNow('2022-01-03 09:00:00');

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn($logo = 'https://fake-logo');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn($model = 'a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturn($modelNote = 'a model note');
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturn($image = 'https://fake-image');
        $oem->shouldReceive('getAttribute')
            ->with('call_group_tags')
            ->once()
            ->andReturn($callGroupTags = 'call group tag fake');
        $oem->shouldReceive('getAttribute')
            ->with('calling_groups')
            ->once()
            ->andReturn($callingGroups = 'calling groups fake');
        $oem->shouldReceive('getAttribute')->with('visited_at')->once()->andReturn(Carbon::now());
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = '123456789');
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn($parts = 3);
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn($manualsCount = 5);

        $resource = new BaseResource($oem);

        $response = $resource->resolve();
        $data     = [
            'id'                     => $id,
            'model'                  => $model,
            'model_notes'            => $modelNote,
            'logo'                   => $logo,
            'image'                  => $image,
            'manuals_count'          => $manualsCount,
            'functional_parts_count' => $parts,
            'call_group_tags'        => $callGroupTags,
            'calling_groups'         => $callingGroups,
            'visited_at'             => Carbon::now(),
        ];
        $this->assertEquals($data, $response);
        $this->jsonSchema(BaseResource::jsonSchema(), true, false);
    }
}
