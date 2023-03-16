<?php

namespace App\Http\Resources\Api\V3\Note;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\NoteResource;
use App\Models\Note;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Note $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private NoteResource $baseResource;

    public function __construct(Note $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new NoteResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return NoteResource::jsonSchema();
    }
}
