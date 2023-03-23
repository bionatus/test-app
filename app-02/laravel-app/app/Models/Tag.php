<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static TagFactory factory()
 *
 * @mixin Tag
 */
class Tag extends Model
{
    const POLYMORPHIC_NAME = 'taggable';
    const TYPE_SERIES      = 'series';
    const TYPE_GENERAL     = 'general';
    const TYPE_ISSUE       = 'issue';
    const TYPE_MORE        = 'more';
    const TYPE_MODEL_TYPE  = 'model_type';
    const MORPH_MODEL_MAPS = [
        self::TYPE_SERIES     => Series::class,
        self::TYPE_GENERAL    => PlainTag::class,
        self::TYPE_ISSUE      => PlainTag::class,
        self::TYPE_MORE       => PlainTag::class,
        self::TYPE_MODEL_TYPE => ModelType::class,
    ];
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts      = [
        'id'          => 'integer',
        'post_id'     => 'integer',
        'taggable_id' => 'integer',
    ];
    public    $timestamps = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
