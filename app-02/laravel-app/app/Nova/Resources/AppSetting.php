<?php

namespace App\Nova\Resources;

use App\Models\AppSetting as AppSettingModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/**
 * @mixin AppSettingModel
 * @property AppSettingModel resource
 */
class AppSetting extends Resource
{
    public static $model  = AppSettingModel::class;
    public static $title  = 'label';
    public static $search = [
        'id',
        'label',
        'value',
    ];
    public static $group  = 'Current';

    public function fields(Request $request)
    {
        switch ($this->resource->type) {
            case 'integer':
                $fieldDynamic = Number::make(__($this->resource->label), 'value')
                    ->rules('nullable', 'integer', 'min:1');
                break;
            default:
                $fieldDynamic = Text::make(__($this->resource->label), 'value')->rules(['required', 'max:255']);
                break;
        }

        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Label'), 'label')->hideWhenCreating()->hideWhenUpdating()->sortable(),
            $fieldDynamic->onlyOnForms(),
            Text::make(__('Value'), 'value_display')
                ->resolveUsing(fn() => $this->resource->value)
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make(__('Type'), 'type')->hideWhenCreating()->hideWhenUpdating(),
        ];
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }
}
