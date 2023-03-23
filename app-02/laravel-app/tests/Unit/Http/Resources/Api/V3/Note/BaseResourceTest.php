<?php

namespace Tests\Unit\Http\Resources\Api\V3\Note;

use App\Http\Resources\Api\V3\Note\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\Note;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $note = Mockery::mock(Note::class);
        $note->shouldReceive('getAttribute')->withArgs(['body'])->once()->andReturn($body = 'body');
        $note->shouldReceive('getAttribute')->withArgs(['link'])->once()->andReturnNull();
        $note->shouldReceive('getAttribute')->withArgs(['link_text'])->once()->andReturnNull();
        $note->shouldReceive('getAttribute')->withArgs(['title'])->once()->andReturn($tittle = 'tittle');
        $note->shouldReceive('getFirstMedia')->withAnyArgs()->once()->andReturnNull();
        $note->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new BaseResource($note))->resolve();

        $data = [
            'id'        => $id,
            'image'     => null,
            'title'     => $tittle,
            'body'      => $body,
            'link'      => null,
            'link_text' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(false);

        $note = Mockery::mock(Note::class);
        $note->shouldReceive('getAttribute')->withArgs(['body'])->once()->andReturn($body = 'body');
        $note->shouldReceive('getAttribute')->withArgs(['link'])->once()->andReturn($link = 'link');
        $note->shouldReceive('getAttribute')->withArgs(['link_text'])->once()->andReturn($linkText = 'link_text');
        $note->shouldReceive('getAttribute')->withArgs(['title'])->once()->andReturn($tittle = 'tittle');
        $note->shouldReceive('getFirstMedia')->withAnyArgs()->once()->andReturn($media);
        $note->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new BaseResource($note))->resolve();

        $data = [
            'id'        => $id,
            'image'     => new ImageResource($media),
            'title'     => $tittle,
            'body'      => $body,
            'link'      => $link,
            'link_text' => $linkText,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
