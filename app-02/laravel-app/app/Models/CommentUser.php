<?php

namespace App\Models;

use Database\Factories\CommentUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CommentUserFactory factory()
 *
 * @mixin CommentUser
 */
class CommentUser extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */
    protected $casts = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'comment_id' => 'integer',
    ];
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
