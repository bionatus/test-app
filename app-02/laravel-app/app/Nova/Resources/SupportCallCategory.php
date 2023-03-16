<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\SupportCallCategory as SupportCallCategoryModel;
use App\Nova\Resource;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @mixin SupportCallCategoryModel */
class SupportCallCategory extends Resource
{
    public static $model  = SupportCallCategoryModel::class;
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
            Text::make(__('Description'), 'description')->hideFromIndex()->rules(['nullable', 'max:255']),
            Text::make(__('Phone'), 'phone')->rules(['required', 'max:255']),
            Number::make(__('Sort'), 'sort')->sortable()->rules(['nullable', 'integer', 'min:1']),
            BelongsTo::make(__('Parent'), 'parent', self::class)->nullable()->readonly(function() {
                return $this->children()->exists();
            }),
            Images::make(__('Image'), MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
            HasMany::make(__('Children'), 'children', self::class)->hideFromDetail(fn() => $this->parent()
                    ->exists() && $this->children()->doesntExist()),
            BelongsToMany::make(__('Instruments'), 'instruments')->hideFromDetail(fn() => $this->children()
                    ->exists() && $this->instruments()->doesntExist()),
        ];
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {
        $query->whereNull('parent_id');

        if ($request->isUpdateOrUpdateAttachedRequest()) {
            $relatableId = $request->get('resourceId');
            $query->where('id', '<>', $relatableId);
        }

        return $query;
    }
}
