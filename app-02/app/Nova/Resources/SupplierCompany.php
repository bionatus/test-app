<?php

namespace App\Nova\Resources;

use App\Models\SupplierCompany as SupplierCompanyModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class SupplierCompany extends Resource
{
    public static $model               = SupplierCompanyModel::class;
    public static $title               = 'name';
    public static $search              = [
        'id',
        'name',
        'email',
    ];
    public static $sort                = [
        'name' => 'asc',
    ];
    public static $displayInNavigation = true;
    public static $group               = 'Current';

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable()->rules('required', 'max:100'),
            Text::make('Email')
                ->rules('nullable', 'max:255', 'bail', 'email:strict', 'ends_with_tld')
                ->creationRules('unique:' . SupplierCompanyModel::tableName() . ',email')
                ->updateRules('unique:' . SupplierCompanyModel::tableName() . ',email,{{resourceId}}'),
        ];
    }
}
