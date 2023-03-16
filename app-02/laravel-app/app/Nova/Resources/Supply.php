<?php

namespace App\Nova\Resources;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Supply extends Resource
{
    public static $model  = \App\Models\Supply::class;
    public static $title  = 'name';
    public static $search = [
        'id',
        'name',
    ];
    public static $group  = 'Current';

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Name'), 'name')->sortable()->rules(['required', 'max:255']),
            Text::make(__('Name for supplier'), 'internal_name')->sortable()->rules(['required', 'max:255']),
            Number::make(__('Sort'), 'sort')->sortable()->rules(['nullable', 'integer', 'min:1']),
            Boolean::make(__('Visible'), 'visible_at')->fillUsing(function(
                $request,
                $model,
                $attribute
            ) {
                if ($request->input($attribute)) {
                    $model->{$attribute} = Carbon::now();

                    return;
                }

                $model->{$attribute} = null;
            }),
            BelongsTo::make(__('Category'), 'supplyCategory', SupplyCategory::class)->sortable()->rules(['required']),
        ];
    }
}
