<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Types\TaggableType;
use Database\Factories\PlainTagFactory;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static PlainTagFactory factory()
 *
 * @mixin PlainTag
 */
class PlainTag extends Model implements IsTaggable
{
    use HasSlug;
    use InteractsWithMedia;

    const MORPH_ALIAS  = 'plain_tag';
    const TYPE_GENERAL = 'general';
    const TYPE_ISSUE   = 'issue';
    const TYPE_MORE    = 'more';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'              => 'integer',
        'posts_count'     => 'integer',
        'followers_count' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function morphType(): string
    {
        return $this->type;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES);
    }

    /**
     * @throws Exception
     */
    public function toTagType(bool $withMedia = false): TaggableType
    {
        $return = [
            'id'   => $this->getRouteKey(),
            'type' => $this->type,
            'name' => $this->name,
        ];

        if ($withMedia) {
            $return['media'] = $this->getFirstMedia(MediaCollectionNames::IMAGES);
        }

        return new TaggableType($return);
    }

    public function taggableRouteKey(): string
    {
        return $this->type . '-' . $this->getRouteKey();
    }

    /* |--- RELATIONS ---| */

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, Tag::POLYMORPHIC_NAME);
    }

    public function posts(): MorphToMany
    {
        return $this->morphToMany(Post::class, Tag::POLYMORPHIC_NAME, Tag::tableName());
    }

    public function userTaggables(): MorphMany
    {
        return $this->morphMany(UserTaggable::class, UserTaggable::POLYMORPHIC_NAME);
    }

    public function followers(): MorphToMany
    {
        return $this->morphToMany(User::class, UserTaggable::POLYMORPHIC_NAME, UserTaggable::tableName());
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
