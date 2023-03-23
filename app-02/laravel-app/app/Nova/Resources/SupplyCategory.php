<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\SupplyCategory as SupplyCategoryModel;
use App\Nova\Resource;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @mixin SupplyCategoryModel */
class SupplyCategory extends Resource
{
    public static $model  = SupplyCategoryModel::class;
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
            Number::make(__('Sort'), 'sort')->sortable()->rules(['nullable', 'integer', 'min:1']),
            Boolean::make('Visible', 'visible_at')
                ->trueValue(Carbon::now())
                ->falseValue(null)
                ->hideFromIndex()
                ->withMeta(['value' => !$this->getKey() || $this->isVisible()]),
            BelongsTo::make(__('Parent'), 'parent', self::class)->nullable()->readonly(function() {
                return !$this->children()->count() == 0;
            }),
            Text::make(__('Children Count'), function() {
                return $this->children()->count();
            }),
            Text::make(__('Supplies Count'), function() {
                return $this->supplies()->count();
            }),
            Images::make(__('Image'), MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
            HasMany::make(__('Supplies'), 'supplies')->hideFromDetail(fn() => $this->children()
                    ->exists() && $this->supplies()->doesntExist()),
            HasMany::make(__('Children'), 'children', self::class)->hideFromDetail(fn() => $this->supplies()
                    ->exists() && $this->children()->doesntExist()),
        ];
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        $pathSegments = explode(DIRECTORY_SEPARATOR, $request->getPathInfo());

        if ($pathSegments[2] == SupplyCategory::uriKey()) {
            return self::relatableQueryForSupplyCategory($request, $query);
        }

        if ($pathSegments[2] == Supply::uriKey()) {
            return self::relatableQueryForSupply($request, $query);
        }

        return $query;
    }

    private static function relatableQueryForSupplyCategory(NovaRequest $request, $query)
    {
        $query->whereDoesntHave('supplies');

        if ($request->isUpdateOrUpdateAttachedRequest()) {
            $relatableId = $request->get('resourceId');
            $query->where('id', '<>', $relatableId);
        }

        return $query;
    }

    private static function relatableQueryForSupply(NovaRequest $request, $query)
    {
        $query->whereDoesntHave('children');

        return $query;
    }
}
