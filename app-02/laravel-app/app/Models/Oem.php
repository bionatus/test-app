<?php

namespace App\Models;

use App\Models\Post\Scopes\ByTaggableTypes;
use App\Models\Product\Scopes\Functional;
use App\Types\TaggableType;
use Database\Factories\OemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @method static OemFactory factory()
 *
 * @mixin Oem
 */
class Oem extends Model
{
    use HasUuid;

    const MORPH_ALIAS                  = 'oem';
    const STATUS_LIVE                  = 'live';
    const STATUS_PENDING               = 'pending';
    const MANUAL_TYPE_GUIDELINES       = 'bluon_guidelines';
    const MANUAL_TYPE_DIAGNOSTIC       = 'diagnostic';
    const MANUAL_TYPE_IOM              = 'iom';
    const MANUAL_TYPE_MISCELLANEOUS    = 'misc';
    const MANUAL_TYPE_PRODUCT_DATA     = 'product_data';
    const MANUAL_TYPE_SERVICE_FACTS    = 'service_facts';
    const MANUAL_TYPE_WIRING_DIAGRAM   = 'wiring_diagram';
    const MANUAL_TYPE_CONTROLS_MANUALS = 'controls_manuals';
    const MANUAL_TYPE_ALL              = [
        self::MANUAL_TYPE_GUIDELINES,
        self::MANUAL_TYPE_DIAGNOSTIC,
        self::MANUAL_TYPE_IOM,
        self::MANUAL_TYPE_MISCELLANEOUS,
        self::MANUAL_TYPE_PRODUCT_DATA,
        self::MANUAL_TYPE_SERVICE_FACTS,
        self::MANUAL_TYPE_WIRING_DIAGRAM,
        self::MANUAL_TYPE_CONTROLS_MANUALS,
    ];
    /* |--- GLOBAL VARIABLES ---| */

    protected $casts = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function functionalPartsCount(): int
    {
        return $this->parts()->scoped(new Functional())->count();
    }

    public function manualsCount(): int
    {
        return Collection::make(self::MANUAL_TYPE_ALL)->sum(function(string $type) {
            return $this->manualType($type)->count();
        });
    }

    public function manualType(string $manualType): Collection
    {
        return Collection::make(explode(';', $this->getAttribute($manualType)))->transform(function($item) {
            return trim($item);
        })->filter();
    }

    public function manuals(): Collection
    {
        $allManuals = Collection::make([]);

        foreach (self::MANUAL_TYPE_ALL as $manualType) {
            $manuals = $this->manualType($manualType);
            $allManuals->put($manualType, $manuals);
        }

        return $allManuals->filter();
    }

    public function postsCount(): int
    {
        if (!$this->series) {
            return 0;
        }

        return Post::scoped(new ByTaggableTypes($this->taggableTypes()))->count();
    }

    private function taggableTypes(): Collection
    {
        if (!($series = $this->series)) {
            return new Collection();
        }

        $modelType = $this->modelType;

        return Collection::make([$series, $modelType])->filter()->map(function(IsTaggable $taggable) {
            if (get_class($taggable) === ModelType::class) {
                return new TaggableType([
                    'id'        => $taggable->getRouteKey(),
                    'type'      => $taggable->morphType(),
                    'connector' => TaggableType::CONNECTOR_OR,
                ]);
            }

            return $taggable->toTagType();
        });
    }

    /* |--- RELATIONS ---| */

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function modelType(): BelongsTo
    {
        return $this->belongsTo(ModelType::class);
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class);
    }

    public function oemParts(): HasMany
    {
        return $this->hasMany(OemPart::class);
    }

    public function oemDetailCounters(): HasMany
    {
        return $this->hasMany(OemDetailCounter::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, OemDetailCounter::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderSnaps(): HasMany
    {
        return $this->hasMany(OrderSnap::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, OemDetailCounter::class)->withTimestamps();
    }

    public function oemUsers(): HasMany
    {
        return $this->hasMany(OemUser::class);
    }

    public function pickerUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, OemUser::class)->withTimestamps();
    }

    public function supportCalls(): HasMany
    {
        return $this->hasMany(SupportCall::class);
    }

    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
