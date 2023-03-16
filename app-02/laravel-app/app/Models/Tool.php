<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use Database\Factories\ToolFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static ToolFactory factory()
 *
 * @mixin Tool
 */
class Tool extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;

    const MORPH_ALIAS = 'tool';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts      = [
        'id' => 'integer',
    ];
    public    $timestamps = false;

    /* |--- FUNCTIONS ---| */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionNames::IMAGES)->registerMediaConversions(function() {
            $this->addMediaConversion(MediaConversionNames::THUMB)
                ->keepOriginalImageFormat()
                ->width(400)
                ->height(400)
                ->nonQueued();
        });
    }

    /* |--- RELATIONS ---| */

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    public function subjectTools(): HasMany
    {
        return $this->hasMany(SubjectTool::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
