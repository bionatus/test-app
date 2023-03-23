<?php

namespace App\Models;

use App\Models\Post\Scopes\ByTaggableTypes;
use App\Types\TaggableType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @method static ProductFactory factory()
 *
 * @mixin Product
 */
class Product extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    protected $casts        = [
        'fields' => 'array',
    ];

    /* |--- FUNCTIONS ---| */

    public function postsCount(): int
    {
        $series        = $this->series;
        $modelTypes    = $series ? $series->oems()->with('modelType')->get()->pluck('modelType') : Collection::make();
        $taggableTypes = $modelTypes->prepend($series)->filter()->map(function(IsTaggable $taggable) {
            if (get_class($taggable) === ModelType::class) {
                return new TaggableType([
                    'id'        => $taggable->getRouteKey(),
                    'type'      => $taggable->morphType(),
                    'connector' => TaggableType::CONNECTOR_OR,
                ]);
            }

            return $taggable->toTagType();
        });

        return Post::scoped(new ByTaggableTypes($taggableTypes))->count();
    }

    /* |--- RELATIONS ---| */

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
