<?php

namespace Database\Seeders\SOP;

use App\Models\AirFilter;
use App\Models\Belt;
use App\Models\Capacitor;
use App\Models\Compressor;
use App\Models\Contactor;
use App\Models\ControlBoard;
use App\Models\CrankcaseHeater;
use App\Models\FanBlade;
use App\Models\FilterDrierAndCore;
use App\Models\GasValve;
use App\Models\HardStartKit;
use App\Models\Igniter;
use App\Models\Item;
use App\Models\MeteringDevice;
use App\Models\Model;
use App\Models\Motor;
use App\Models\OemPart;
use App\Models\Other;
use App\Models\Part;
use App\Models\PressureControl;
use App\Models\RelaySwitchTimerSequencer;
use App\Models\Sensor;
use App\Models\SheaveAndPulley;
use App\Models\TemperatureControl;
use App\Models\Wheel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Str;

class ItemPartGenerator
{
    private Collection $brands;
    private Collection $oems;
    private Collection $items;
    private Collection $parts;
    const TYPE_CLASSES = [
        AirFilter::MORPH_ALIAS                 => AirFilter::class,
        Belt::MORPH_ALIAS                      => Belt::class,
        Capacitor::MORPH_ALIAS                 => Capacitor::class,
        Compressor::MORPH_ALIAS                => Compressor::class,
        Contactor::MORPH_ALIAS                 => Contactor::class,
        ControlBoard::MORPH_ALIAS              => ControlBoard::class,
        CrankcaseHeater::MORPH_ALIAS           => CrankcaseHeater::class,
        FanBlade::MORPH_ALIAS                  => FanBlade::class,
        FilterDrierAndCore::MORPH_ALIAS        => FilterDrierAndCore::class,
        GasValve::MORPH_ALIAS                  => GasValve::class,
        HardStartKit::MORPH_ALIAS              => HardStartKit::class,
        Igniter::MORPH_ALIAS                   => Igniter::class,
        MeteringDevice::MORPH_ALIAS            => MeteringDevice::class,
        Motor::MORPH_ALIAS                     => Motor::class,
        Other::MORPH_ALIAS                     => Other::class,
        PressureControl::MORPH_ALIAS           => PressureControl::class,
        RelaySwitchTimerSequencer::MORPH_ALIAS => RelaySwitchTimerSequencer::class,
        Sensor::MORPH_ALIAS                    => Sensor::class,
        SheaveAndPulley::MORPH_ALIAS           => SheaveAndPulley::class,
        TemperatureControl::MORPH_ALIAS        => TemperatureControl::class,
        Wheel::MORPH_ALIAS                     => Wheel::class,
    ];

    public function __construct(Collection $brands, Collection $oems)
    {
        $this->brands = $brands->flatten();
        $this->oems   = $oems;
        $this->items  = new Collection();
        $this->parts  = new Collection();
    }

    public function createItemsParts()
    {
        $partTypes = Part::TYPES;

        foreach ($partTypes as $type) {
            $this->brands->each(function($brand) use ($type) {
                $this->createPart($type, $brand);
            });
        }
    }

    private function createPart($type, $brand)
    {
        /** @var Model $classType */
        $classType = self::TYPE_CLASSES[$type];
        $uuid      = Str::uuidFromString($brand->slug . '-' . $type);

        $item = Item::where('uuid', $uuid)->first();
        if (!$item) {
            $item = Item::factory()->part()->create(['uuid' => $uuid]);
            $part = Part::factory()->create([
                'id'           => $item->getKey(),
                'type'         => $type,
                'brand'        => Str::upper($brand->name),
                'published_at' => Carbon::now(),
            ]);
            ($classType)::factory()->usingPart($part)->create();
            $oem = Collection::make($this->oems->get($brand->name))->first();

            OemPart::factory()->usingPart($part)->usingOem($oem)->create();
        }

        $part = $item->part;
        $this->items->put($item->getKey(), $item);
        $this->parts->put($part->getKey(), $part);
    }
}
