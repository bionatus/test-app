<?php

namespace App\Models;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static SubjectFactory factory()
 *
 * @mixin Subject
 */
class Subject extends Model implements HasMedia
{
    use HasSlug;
    use InteractsWithMedia;

    const MORPH_ALIAS   = 'subject';
    const TYPE_TOPIC    = 'topic';
    const TYPE_SUBTOPIC = 'subtopic';
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts      = [
        'id' => 'integer',
    ];
    public    $timestamps = false;

    /* |--- FUNCTIONS ---| */

    public function isTopic(): bool
    {
        return self::TYPE_TOPIC === $this->type;
    }

    public function isSubTopic(): bool
    {
        return self::TYPE_SUBTOPIC === $this->type;
    }

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
            $this->addMediaConversion(MediaConversionNames::THUMB)->width(400)->height(400)->nonQueued();
        });
    }

    /* |--- RELATIONS ---| */

    public function topic(): HasOne
    {
        return $this->hasOne(Topic::class, Topic::keyName(), self::keyName());
    }

    public function subtopic(): HasOne
    {
        return $this->hasOne(Subtopic::class, Subtopic::keyName(), self::keyName());
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class);
    }

    public function subjectTools(): HasMany
    {
        return $this->hasMany(SubjectTool::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
