<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static NoteFactory factory()
 *
 * @mixin Note
 */
class Note extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;

    /* |--- CONSTANTS ---| */
    const MORPH_ALIAS            = 'note';
    const SLUG_GAMIFICATION_NOTE = 'gamification-note';
    /* |--- GLOBAL VARIABLES ---| */
    /* |--- FUNCTIONS ---| */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->singleFile()->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    /* |--- RELATIONS ---| */
    public function noteCategory(): BelongsTo
    {
        return $this->belongsTo(NoteCategory::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
