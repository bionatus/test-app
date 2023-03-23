<?php

namespace Tests\Unit\Types;

use App\Types\LinkResourceType;
use Tests\TestCase;

class LinkResourceTypeTest extends TestCase
{
    /** @test */
    public function it_returns_an_array_representation()
    {
        $id    = 'a given uuid';
        $type  = 'a given type';
        $event = 'a given event';
        $data  = ['data_item' => 'a data string'];

        $expected = [
            'id'        => $id,
            'type'      => $type,
            'event'     => $event,
            'data_item' => $data['data_item'],
        ];

        $linkResourceType = new LinkResourceType($event, $type, $id, $data);

        $this->assertIsArray($linkResourceType->toArray());
        $this->assertArrayHasKeysAndValues($expected, $linkResourceType->toArray());
    }

    /** @test */
    public function it_returns_a_json_representation()
    {
        $id    = 'a given uuid';
        $type  = 'a given type';
        $event = 'a given event';
        $data  = ['data_item' => 'a data string'];

        $expected = [
            'id'        => $id,
            'type'      => $type,
            'event'     => $event,
            'data_item' => $data['data_item'],
        ];

        $linkResourceType = new LinkResourceType($event, $type, $id, $data);

        $this->assertIsString($linkResourceType->toJson());
        $this->assertArrayHasKeysAndValues($expected, json_decode($linkResourceType->toJson(), true));

        $schema = $this->jsonSchema(LinkResourceType::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode($linkResourceType->toJson()));
    }

    /** @test */
    public function it_does_not_have_additional_items_when_data_is_null()
    {
        $id    = 'a given uuid';
        $type  = 'a given type';
        $event = 'a given event';
        $data  = null;

        $expected = [
            'id'    => $id,
            'type'  => $type,
            'event' => $event,
        ];

        $linkResourceType = new LinkResourceType($event, $type, $id, $data);

        $this->assertIsArray($linkResourceType->toArray());
        $this->assertArrayHasKeysAndValues($expected, $linkResourceType->toArray());

        $this->assertIsString($linkResourceType->toJson());
        $this->assertArrayHasKeysAndValues($expected, json_decode($linkResourceType->toJson(), true));

        $schema = $this->jsonSchema(LinkResourceType::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode($linkResourceType->toJson()));
    }
}
