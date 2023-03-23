<?php

namespace App\Models;

use Database\Factories\PostVoteFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static PostVoteFactory factory()
 *
 * @mixin PostVote
 */
class PostVote extends Model
{
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'      => 'integer',
        'user_id' => 'integer',
        'post_id' => 'integer',
    ];
    protected $table = 'post_votes';
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
