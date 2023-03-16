<?php

namespace App\Nova\Resources\User;

use App\Models\Supplier;
use App\Models\Supplier as SupplierModel;
use App\Nova\Resources\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Str;

/** @mixin SupplierModel */
class UserSupplier extends Resource
{
    public static $model               = SupplierModel::class;
    public static $title               = 'name';
    public static $search              = [
        'id',
        'airtable_id',
        'name',
        'email',
        'address',
        'city',
    ];
    public static $group               = 'Current';
    public static $displayInNavigation = false;

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable()->rules('required', 'max:255'),
            Text::make('Address'),
            Boolean::make('Visible By User')->displayUsing(function($a) {
                return $this->pivot->visible_by_user;
            }),
            Number::make('Orders')->displayUsing(fn() => $this->orders()
                ->where('user_id', $this->pivot->user_id)
                ->count()),
        ];
    }

    public static function label()
    {
        return Str::plural(Supplier::MORPH_ALIAS);
    }

    public function subtitle()
    {
        return implode(', ', array_filter([
            $this->id,
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country,
        ]));
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }
}
