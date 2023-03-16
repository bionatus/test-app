<?php

namespace App\Types;

use App\Models\IsTaggable;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

class TaggablesCollection extends EloquentCollection
{
    /**
     * @param array $items
     *
     * @throws Exception
     */
    public function __construct($items = [])
    {
        $this->validateItems($items);

        parent::__construct($items);
    }

    /**
     * @throws Exception
     */
    public function pushRawTag(...$rawTags): self
    {
        $taggables = [];
        foreach ($rawTags as $rawTag) {
            $taggableType = new TaggableType($rawTag);
            $taggables[]  = $taggableType->taggable();
        }

        return $this->push(...$taggables);
    }

    /**
     * @param mixed ...$values [optional]
     *
     * @return TaggablesCollection
     * @throws Exception
     */
    public function push(...$values)
    {
        $this->validateItems($values);

        return parent::push(...$values);
    }

    /**
     * @throws Exception
     */
    public static function fromRaw(array $rawTags = []): self
    {
        $collection = self::make();

        return $collection->pushRawTag(...$rawTags);
    }

    /**
     * @throws Exception
     */
    private function validateItems($items): void
    {
        foreach ($items as $item) {
            $this->validateItem($item);
        }
    }

    /**
     * @throws Exception
     */
    private function validateItem($item): void
    {
        if (!($item instanceof IsTaggable)) {
            throw new Exception("Invalid taggable type.");
        }
    }

    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        $result = new parent(array_combine($keys, $items));

        return $result->contains(function ($item) {
            return !$item instanceof Model;
        }) ? $result->toBase() : $result;
    }
}
