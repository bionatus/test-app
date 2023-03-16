<?php

namespace App\Types;

use App\Models\IsTaggable;
use App\Models\Media;
use App\Models\Tag;
use App\Services\TaggableQuery;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TaggableType
{
    const CONNECTOR_OR  = 'OR';
    const CONNECTOR_AND = 'AND';
    public string  $id;
    public string  $name;
    public string  $type;
    private ?Media $media = null;
    private string $connector;
    private ?IsTaggable $taggable;

    /**
     * @throws Exception
     */
    public function __construct(array $item)
    {
        if (empty($item['id']) || empty($item['type'])) {
            throw new Exception('Invalid element. The element needs to have an id and type.');
        }

        if (!in_array($item['type'], array_keys(Tag::MORPH_MODEL_MAPS))) {
            throw new Exception('Invalid type.');
        }

        if (array_key_exists('connector', $item) && !in_array($item['connector'],
                [self::CONNECTOR_AND, self::CONNECTOR_OR])) {
            throw new Exception('Invalid connector.');
        }

        $this->id        = $item['id'];
        $this->name      = $item['name'] ?? $this->id;
        $this->type      = $item['type'];
        $this->connector = $item['connector'] ?? self::CONNECTOR_AND;

        if (array_key_exists('media', $item) && is_a($item['media'], Media::class)) {
            $this->media = $item['media'];
        }
    }

    public static function query(string $typeFilter = null, string $parentFilter = null): TaggableQuery
    {
        return new TaggableQuery($typeFilter, $parentFilter);
    }

    /**
     * @return IsTaggable|Model|null
     */
    public function taggable(): ?IsTaggable
    {
        /** @var Model $modelClass */
        $modelClass = Tag::MORPH_MODEL_MAPS[$this->type] ?? null;

        if (!$modelClass) {
            return null;
        }

        $this->taggable = $this->taggable ?? $modelClass::query()
                ->where($modelClass::routeKeyName(), $this->id)
                ->first();

        return $this->taggable;
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }

    public function getMedia(): array
    {
        return null === $this->media ? [] : [$this->media];
    }

    public function connector(): string
    {
        return $this->connector;
    }
}
