<?php

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static TopicFactory factory()
 *
 * @mixin Topic
 */
class Topic extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts        = [
        'id' => 'integer',
    ];
    public    $timestamps   = false;
    public    $incrementing = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, self::keyName());
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
