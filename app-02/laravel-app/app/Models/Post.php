<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Models\Comment\Scopes\Solution;
use App\Models\Scopes\ByUser;
use Auth;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static PostFactory factory()
 *
 * @mixin Post
 */
class Post extends Model implements HasMedia
{
    use HasUuid;
    use InteractsWithMedia;

    const MORPH_ALIAS     = 'post';
    const TYPE_NEEDS_HELP = 'needs-help';
    const TYPE_FUNNY      = 'funny';
    const TYPE_OTHER      = 'other';
    /* |--- GLOBAL VARIABLES ---| */

    protected $attributes = [
        'type' => Post::TYPE_OTHER,
    ];
    protected $casts      = [
        'id'             => 'integer',
        'uuid'           => 'string',
        'user_id'        => 'integer',
        'comments_count' => 'integer',
        'votes_count'    => 'integer',
        'pinned'         => 'boolean',
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

    public function isSolved(): bool
    {
        return !!$this->solutionComment;
    }

    /* |--- RELATIONS ---| */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function solutionComment(): HasOne
    {
        return $this->hasOne(Comment::class)->scoped(new Solution());
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class)->whereHas('taggable');
    }

    public function tagSeries(): MorphToMany
    {
        return $this->morphedByMany(Series::class, Tag::POLYMORPHIC_NAME, Tag::tableName());
    }

    public function tagPlainTags(): MorphToMany
    {
        return $this->morphedByMany(PlainTag::class, Tag::POLYMORPHIC_NAME, Tag::tableName());
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PostVote::class);
    }

    public function authUserVote(): HasOne
    {
        $user = Auth::user() ?? new User();

        return $this->hasOne(PostVote::class)->scoped(new ByUser($user));
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
