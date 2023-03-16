<?php

namespace App\Constants;

use App\Models\AirFilter;
use App\Models\Belt;
use App\Models\Brand;
use App\Models\Capacitor;
use App\Models\CartItem;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Compressor;
use App\Models\Contactor;
use App\Models\ControlBoard;
use App\Models\CrankcaseHeater;
use App\Models\CurriDelivery;
use App\Models\CustomItem;
use App\Models\FanBlade;
use App\Models\FilterDrierAndCore;
use App\Models\GasValve;
use App\Models\HardStartKit;
use App\Models\Igniter;
use App\Models\Instrument;
use App\Models\ItemOrder;
use App\Models\MeteringDevice;
use App\Models\ModelType;
use App\Models\Motor;
use App\Models\Note;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Other;
use App\Models\OtherDelivery;
use App\Models\Part;
use App\Models\PartBrand;
use App\Models\Phone;
use App\Models\Pickup;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\PressureControl;
use App\Models\RelaySwitchTimerSequencer;
use App\Models\Sensor;
use App\Models\Series;
use App\Models\SheaveAndPulley;
use App\Models\ShipmentDelivery;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\SupportCallCategory;
use App\Models\TemperatureControl;
use App\Models\Ticket;
use App\Models\Tool;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Models\Wheel;
use App\Models\XoxoRedemption;

class RelationsMorphs
{
    const MAP = [
        AirFilter::MORPH_ALIAS                 => AirFilter::class,
        Belt::MORPH_ALIAS                      => Belt::class,
        Brand::MORPH_ALIAS                     => Brand::class,
        Capacitor::MORPH_ALIAS                 => Capacitor::class,
        CartItem::MORPH_ALIAS                  => CartItem::class,
        Comment::MORPH_ALIAS                   => Comment::class,
        Company::MORPH_ALIAS                   => Company::class,
        Compressor::MORPH_ALIAS                => Compressor::class,
        Contactor::MORPH_ALIAS                 => Contactor::class,
        ControlBoard::MORPH_ALIAS              => ControlBoard::class,
        CrankcaseHeater::MORPH_ALIAS           => CrankcaseHeater::class,
        CurriDelivery::MORPH_ALIAS             => CurriDelivery::class,
        CustomItem::MORPH_ALIAS                => CustomItem::class,
        FanBlade::MORPH_ALIAS                  => FanBlade::class,
        FilterDrierAndCore::MORPH_ALIAS        => FilterDrierAndCore::class,
        GasValve::MORPH_ALIAS                  => GasValve::class,
        HardStartKit::MORPH_ALIAS              => HardStartKit::class,
        Igniter::MORPH_ALIAS                   => Igniter::class,
        Instrument::MORPH_ALIAS                => Instrument::class,
        ItemOrder::MORPH_ALIAS                 => ItemOrder::class,
        MeteringDevice::MORPH_ALIAS            => MeteringDevice::class,
        ModelType::MORPH_ALIAS                 => ModelType::class,
        Motor::MORPH_ALIAS                     => Motor::class,
        Note::MORPH_ALIAS                      => Note::class,
        Oem::MORPH_ALIAS                       => Oem::class,
        Order::MORPH_ALIAS                     => Order::class,
        OrderDelivery::MORPH_ALIAS             => OrderDelivery::class,
        Other::MORPH_ALIAS                     => Other::class,
        OtherDelivery::MORPH_ALIAS             => OtherDelivery::class,
        PartBrand::MORPH_ALIAS                 => PartBrand::class,
        Part::MORPH_ALIAS                      => Part::class,
        Phone::MORPH_ALIAS                     => Phone::class,
        Pickup::MORPH_ALIAS                    => Pickup::class,
        PlainTag::MORPH_ALIAS                  => PlainTag::class,
        Post::MORPH_ALIAS                      => Post::class,
        PressureControl::MORPH_ALIAS           => PressureControl::class,
        RelaySwitchTimerSequencer::MORPH_ALIAS => RelaySwitchTimerSequencer::class,
        Sensor::MORPH_ALIAS                    => Sensor::class,
        Series::MORPH_ALIAS                    => Series::class,
        SheaveAndPulley::MORPH_ALIAS           => SheaveAndPulley::class,
        ShipmentDelivery::MORPH_ALIAS          => ShipmentDelivery::class,
        Staff::MORPH_ALIAS                     => Staff::class,
        Subject::MORPH_ALIAS                   => Subject::class,
        Supplier::MORPH_ALIAS                  => Supplier::class,
        SupplyCategory::MORPH_ALIAS            => SupplyCategory::class,
        Supply::MORPH_ALIAS                    => Supply::class,
        SupportCallCategory::MORPH_ALIAS       => SupportCallCategory::class,
        TemperatureControl::MORPH_ALIAS        => TemperatureControl::class,
        Ticket::MORPH_ALIAS                    => Ticket::class,
        Tool::MORPH_ALIAS                      => Tool::class,
        User::MORPH_ALIAS                      => User::class,
        WarehouseDelivery::MORPH_ALIAS         => WarehouseDelivery::class,
        Wheel::MORPH_ALIAS                     => Wheel::class,
        XoxoRedemption::MORPH_ALIAS            => XoxoRedemption::class,
    ];
}
