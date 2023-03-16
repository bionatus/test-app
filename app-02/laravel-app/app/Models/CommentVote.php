<?php

namespace App\Models;

use Database\Factories\CommentVoteFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static CommentVoteFactory factory()
 *
 * @mixin CommentVote
 */
class CommentVote extends Pivot
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $table = 'comment_votes';
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
