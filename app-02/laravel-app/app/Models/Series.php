<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Types\TaggableType;
use Database\Factories\SeriesFactory;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static SeriesFactory factory()
 *
 * @mixin Series
 */
class Series extends Model implements IsTaggable
{
    use InteractsWithMedia;

    const MORPH_ALIAS = 'series';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id'              => 'integer',
        'uuid'            => 'string',
        'brand_id'        => 'integer',
        'posts_count'     => 'integer',
        'followers_count' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function morphType(): string
    {
        return self::MORPH_ALIAS;
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
        return new TaggableType([
            'id'   => $this->getRouteKey(),
            'type' => self::MORPH_ALIAS,
            'name' => $this->compositeName(),
        ]);
    }

    public function taggableRouteKey(): string
    {
        return Tag::TYPE_SERIES . '-' . $this->getRouteKey();
    }

    public function compositeName(): string
    {
        $brandName = $this->brand ? $this->brand->name : '';

        return $brandName . '|' . $this->name;
    }

    /* |--- RELATIONS ---| */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, Tag::POLYMORPHIC_NAME);
    }

    public function posts(): MorphToMany
    {
        return $this->morphToMany(Post::class, Tag::POLYMORPHIC_NAME, Tag::tableName());
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function userTaggables(): MorphMany
    {
        return $this->morphMany(UserTaggable::class, UserTaggable::POLYMORPHIC_NAME);
    }

    public function followers(): MorphToMany
    {
        return $this->morphToMany(User::class, UserTaggable::POLYMORPHIC_NAME, UserTaggable::tableName());
    }

    public function seriesUsers(): HasMany
    {
        return $this->hasMany(SeriesUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function oems(): HasMany
    {
        return $this->hasMany(Oem::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
