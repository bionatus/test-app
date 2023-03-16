<?php

namespace App\Types;

use App\Models\Model;
use App\Models\Oem;
use App\Models\Part;
use Exception;
use App\Services\OemPartQuery;

class RecentlyViewed
{
    public int    $object_id;
    public string $object_type;
    public ?Model $object;
    public string $viewed_at;

    /**
     * @throws Exception
     */
    public function __construct(array $item)
    {
        if (empty($item['object_type']) || empty($item['object_id']) || empty($item['viewed_at'])) {
            throw new Exception('Invalid element. The element needs to have a type, id and a date viewed.');
        }

        if (!in_array($item['object_type'], [Oem::MORPH_ALIAS, Part::MORPH_ALIAS])) {
            throw new Exception('Invalid type.');
        }

        $this->object_id   = $item['object_id'];
        $this->object_type = $item['object_type'];
        $this->object      = $item['object'] ?? null;
        $this->viewed_at   = $item['viewed_at'];
    }

    public function toArray(): array
    {
        return [
            'object_id'   => $this->object_id,
            'object_type' => $this->object_type,
            'object'      => $this->object,
            'viewed_at'   => $this->viewed_at,
        ];
    }

    public function objectId(): int
    {
        return $this->object_id;
    }

    public function objectType(): string
    {
        return $this->object_type;
    }

    public function object(): ?Model
    {
        return $this->object;
    }

    public function viewedAt(): string
    {
        return $this->viewed_at;
    }

    public static function query(string $userId): OemPartQuery
    {
        return new OemPartQuery($userId);
    }
}
