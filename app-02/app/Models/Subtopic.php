<?php

namespace App\Models;

use Database\Factories\SubtopicFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SubtopicFactory factory()
 *
 * @mixin Subtopic
 */
class Subtopic extends Model
{
    const MORPH_ALIAS = 'subtopic';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts        = [
        'id'       => 'integer',
        'topic_id' => 'integer',
    ];
    public    $timestamps   = false;
    public    $incrementing = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, self::keyName());
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
