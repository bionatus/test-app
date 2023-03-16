<?php

namespace App\Models;

use Database\Factories\SubjectToolFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static SubjectToolFactory factory()
 *
 * @mixin SubjectTool
 */
class SubjectTool extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts      = [
        'id'         => 'integer',
        'subject_id' => 'integer',
        'tool_id'    => 'integer',
    ];
    public    $timestamps = false;
    /* |--- FUNCTIONS ---| */

    /* |--- RELATIONS ---| */

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }


    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
