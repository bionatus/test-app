<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Term;

use App\Http\Resources\Api\V3\Account\Term\BaseResource;
use App\Models\Term;
use App\Models\TermUser;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $objectiveTerm = Mockery::mock(Term::class);
        $objectiveTerm->shouldReceive('getAttribute')->withArgs(['title'])->once()->andReturn($title="tiotle");

        $termUser = Mockery::mock(TermUser::class);
        $termUser->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 70);
        $termUser->shouldReceive('getAttribute')->withArgs(['term'])->once()->andReturn($objectiveTerm);

        $resource = new BaseResource($termUser);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'title' => $title,
        ];

        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
        $this->assertEquals($data, $response);
    }
}
