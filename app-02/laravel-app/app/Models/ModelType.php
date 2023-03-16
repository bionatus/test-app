<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Types\TaggableType;
use Database\Factories\ModelTypeFactory;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static ModelTypeFactory factory()
 *
 * @mixin ModelType
 */
class ModelType extends Model implements HasMedia, IsTaggable
{
    use HasSlug;
    use InteractsWithMedia;

    const MORPH_ALIAS = 'model_type';

    /* |--- FUNCTIONS ---| */
    public function morphType(): string
    {
        return self::MORPH_ALIAS;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @throws Exception
     */
    public function toTagType(bool $withMedia = false): TaggableType
    {
        $tag = [
            'id'   => $this->getRouteKey(),
            'type' => self::MORPH_ALIAS,
            'name' => $this->name,
        ];

        if ($withMedia) {
            $tag['media'] = $this->getFirstMedia(MediaCollectionNames::IMAGES);
        }

        return new TaggableType($tag);
    }

    public function taggableRouteKey(): string
    {
        return Tag::TYPE_MODEL_TYPE . '-' . $this->getRouteKey();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES);
    }

    public function oemsCount(): int
    {
        return $this->oems()->count();
    }

    /* |--- RELATIONS ---| */
    public function oems(): HasMany
    {
        return $this->hasMany(Oem::class);
    }

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
}
