<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Models\Scopes\ByUser;
use Auth;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static CommentFactory factory()
 *
 * @mixin Comment
 */
class Comment extends Model implements HasMedia
{
    use HasUuid;
    use InteractsWithMedia;

    const MORPH_ALIAS = 'comment';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'post_id'     => 'integer',
        'uuid'        => 'string',
        'solution'    => 'boolean',
        'votes_count' => 'integer',
    ];
    protected $dates = [
        'content_updated_at',
        'created_at',
        'updated_at',
    ];

    /* |--- FUNCTIONS ---| */

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    public function isSolution(): bool
    {
        return !!$this->solution;
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function authUserVote(): HasOne
    {
        $user = Auth::user() ?? new User();

        return $this->hasOne(CommentVote::class)->scoped(new ByUser($user));
    }

    public function latestFiveVotes(): HasMany
    {
        return $this->votes()->latest()->limit(5);
    }

    public function commentUsers(): HasMany
    {
        return $this->hasMany(CommentUser::class);
    }

    public function taggedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
