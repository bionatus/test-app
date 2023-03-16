<?php

namespace App\Nova\Resources;

use App;
use App\Models\Point as PointModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/** @mixin PointModel */
class Point extends Resource
{
    public static $model               = PointModel::class;
    public static $title               = 'name';
    public static $search              = [
        'points_earned',
        'action',
        'created_at',
    ];
    public static $displayInNavigation = false;

    public static function newModel()
    {
        $model = static::$model;

        return new $model;
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable()->hideFromIndex(),
            Number::make('Points Earned')->step(1),
            Text::make('Action')->hideWhenCreating(),
            Date::make('Created At')->hideWhenCreating(),
        ];
    }

    public static function redirectAfterCreate(Request $request, $resource)
    {
        return '/resources/' . $request->viaResource . '/' . $request->viaResourceId;
    }
}
