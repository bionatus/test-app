<?php

namespace App\Nova\Resources;

use App\Models\SupplierHour as SupplierHourModel;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;

class SupplierHour extends Resource
{
    public static $model               = SupplierHourModel::class;
    public static $title               = 'day';
    public static $search              = [
        'id',
        'day',
    ];
    public static $sort                = [
        'id' => 'asc',
    ];
    public static $displayInNavigation = false;

    private function getValidDays(): Collection
    {
        $days = [
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        ];

        return Collection::make($days);
    }

    private function getIntervals(): Collection
    {
        $period    = new CarbonPeriod('00:00', '30 minutes', '24:00');
        $intervals = [];
        foreach ($period as $interval) {
            $interval                      = $interval->format("g:i a");
            $intervals[(string) $interval] = $interval;
        }

        return Collection::make($intervals);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {

        $days      = $this->getValidDays();
        $intervals = $this->getIntervals();

        return [
            ID::make(__('ID'), 'id')->sortable()->hideFromIndex(),
            Select::make('Day')->options($days)->displayUsingLabels()->hideWhenUpdating(),
            Select::make('From')->options($intervals),
            Select::make('To')->options($intervals),
        ];
    }
}
