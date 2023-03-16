<?php

namespace App\Models;

use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\InteractsWithMedia;
use Str;

/**
 * @method static PartFactory factory()
 *
 * @mixin Part
 */
class Part extends Model implements IsOrderable
{
    use InteractsWithMedia;

    const MORPH_ALIAS                       = 'part';
    const TYPE_AIR_FILTER                   = AirFilter::MORPH_ALIAS;
    const TYPE_BELT                         = Belt::MORPH_ALIAS;
    const TYPE_CAPACITOR                    = Capacitor::MORPH_ALIAS;
    const TYPE_COMPRESSOR                   = Compressor::MORPH_ALIAS;
    const TYPE_CONTACTOR                    = Contactor::MORPH_ALIAS;
    const TYPE_CONTROL_BOARD                = ControlBoard::MORPH_ALIAS;
    const TYPE_CRANKCASE_HEATER             = CrankcaseHeater::MORPH_ALIAS;
    const TYPE_FAN_BLADE                    = FanBlade::MORPH_ALIAS;
    const TYPE_FILTER_DRIER_AND_CORE        = FilterDrierAndCore::MORPH_ALIAS;
    const TYPE_GAS_VALVE                    = GasValve::MORPH_ALIAS;
    const TYPE_HARD_START_KIT               = HardStartKit::MORPH_ALIAS;
    const TYPE_IGNITER                      = Igniter::MORPH_ALIAS;
    const TYPE_METERING_DEVICE              = MeteringDevice::MORPH_ALIAS;
    const TYPE_MOTOR                        = Motor::MORPH_ALIAS;
    const TYPE_PRESSURE_CONTROL             = PressureControl::MORPH_ALIAS;
    const TYPE_RELAY_SWITCH_TIMER_SEQUENCER = RelaySwitchTimerSequencer::MORPH_ALIAS;
    const TYPE_SENSOR                       = Sensor::MORPH_ALIAS;
    const TYPE_SHEAVE_AND_PULLEY            = SheaveAndPulley::MORPH_ALIAS;
    const TYPE_TEMPERATURE_CONTROL          = TemperatureControl::MORPH_ALIAS;
    const TYPE_WHEEL                        = Wheel::MORPH_ALIAS;
    const TYPE_OTHER                        = Other::MORPH_ALIAS;
    const TYPES                             = [...self::FUNCTIONAL_TYPES, self::TYPE_OTHER];
    const FUNCTIONAL_TYPES                  = [
        self::TYPE_AIR_FILTER,
        self::TYPE_BELT,
        self::TYPE_CAPACITOR,
        self::TYPE_COMPRESSOR,
        self::TYPE_CONTACTOR,
        self::TYPE_CONTROL_BOARD,
        self::TYPE_CRANKCASE_HEATER,
        self::TYPE_FAN_BLADE,
        self::TYPE_FILTER_DRIER_AND_CORE,
        self::TYPE_GAS_VALVE,
        self::TYPE_HARD_START_KIT,
        self::TYPE_IGNITER,
        self::TYPE_METERING_DEVICE,
        self::TYPE_MOTOR,
        self::TYPE_PRESSURE_CONTROL,
        self::TYPE_RELAY_SWITCH_TIMER_SEQUENCER,
        self::TYPE_SENSOR,
        self::TYPE_SHEAVE_AND_PULLEY,
        self::TYPE_TEMPERATURE_CONTROL,
        self::TYPE_WHEEL,
    ];
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    public    $timestamps   = false;
    protected $casts        = [
        'id' => 'integer',
    ];

    /* |--- FUNCTIONS ---| */

    public function hasValidType(): bool
    {
        return in_array($this->type, self::TYPES);
    }

    public function isOther(): bool
    {
        return $this->detail instanceof Other;
    }

    public function hiddenNumber(): string
    {
        return Str::padRight(Str::substr($this->number, 0, 3), 15, '*');
    }

    /* |--- RELATIONS ---| */

    public function item(): HasOne
    {
        return $this->hasOne(Item::class, 'id');
    }

    public function oems(): BelongsToMany
    {
        return $this->belongsToMany(Oem::class);
    }

    public function oemParts(): HasMany
    {
        return $this->hasMany(OemPart::class);
    }

    public function detail(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'type', 'id');
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(Replacement::class, 'original_part_id');
    }

    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }

    public function partDetailCounters(): HasMany
    {
        return $this->hasMany(PartDetailCounter::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, PartDetailCounter::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, PartDetailCounter::class)->withTimestamps();
    }

    public function note(): hasOne
    {
        return $this->hasOne(PartNote::class);
    }

    public function recommendedReplacements(): HasMany
    {
        return $this->hasMany(RecommendedReplacement::class, 'original_part_id');
    }

    /* |--- ACCESSORS ---| */
    public function getReadableTypeAttribute(): string
    {
        return Str::of($this->type)->replace('_', ' ')->title();
    }
    /* |--- MUTATORS ---| */

}
