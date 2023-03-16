<?php

namespace Tests\Unit\Http\Resources\BasecampApi\V1\User;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Http\Resources\BasecampApi\V1\User\BriefResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\User;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BriefResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BriefResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getFirstMedia')->with('images')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);

        $response = (new BriefResource($user))->resolve();

        $data = [
            'id'         => $id,
            'avatar'     => null,
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BriefResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user  = Mockery::mock(User::class);
        $media = Mockery::mock(Media::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);

        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media-uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media-url');
        $media->shouldReceive('hasGeneratedConversion')->with(MediaConversionNames::THUMB)->once()->andReturnFalse();

        $response = (new BriefResource($user))->resolve();

        $data = [
            'id'         => $id,
            'avatar'     => new ImageResource($media),
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BriefResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
