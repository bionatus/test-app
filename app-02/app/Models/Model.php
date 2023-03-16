<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Str;

/**
 * Class Model
 *
 * @method static static create($attributes = [])
 *
 * @package App\Models
 */
abstract class Model extends BaseModel
{
    use HasFactory;

    const ATTRIBUTE_COUNT_SUFFIX = '_count';
    protected $guarded = [];

    public static function tableName(): string
    {
        return (new static())->getTable();
    }

    public static function routeKeyName(): string
    {
        return (new static())->getRouteKeyName();
    }

    public static function keyName(): string
    {
        return (new static())->getKeyName();
    }

    public function loadMissingCount(string $relationship): self
    {
        $attribute = Str::snake($relationship) . self::ATTRIBUTE_COUNT_SUFFIX;

        if (null === $this->getAttribute($attribute)) {
            $this->loadCount($relationship);
        }

        return $this;
    }
}
