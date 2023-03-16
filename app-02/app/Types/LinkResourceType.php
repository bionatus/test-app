<?php

namespace App\Types;

use App\Http\Resources\HasJsonSchema;

class LinkResourceType implements HasJsonSchema
{
    private string $event;
    private string $type;
    private string $id;
    private array  $data;

    public function __construct(string $event, string $type, string $id, ?array $data = null)
    {
        $this->event = $event;
        $this->type  = $type;
        $this->id    = $id;
        $this->data  = $data ?? [];
    }

    public function toArray(): array
    {
        return array_merge([
            'event' => $this->event,
            'type'  => $this->type,
            'id'    => $this->id,
        ], $this->data);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'event' => ['type' => ['string']],
                'type'  => ['type' => ['string']],
                'id'    => ['type' => ['string']],
            ],
            'required'             => [
                'event',
                'type',
                'id',
            ],
            'additionalProperties' => true,
        ];
    }
}
