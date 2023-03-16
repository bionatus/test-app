<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\PlainTag as PlainTagModel;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Epartment\NovaDependencyContainer\HasDependencies;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @mixin PlainTagModel */
class PlainTag extends Resource
{
    use HasDependencies;

    public static $model  = PlainTagModel::class;
    public static $title  = 'name';
    public static $search = [
        'id',
        'name',
    ];
    public static $group  = 'Current';

    public static function label()
    {
        return 'Tags';
    }

    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Title'), 'name')->sortable()->rules('required', 'max:50'),
            Images::make('Photo', MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->singleMediaRules(['mimes:jpg,jpeg,png'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', PlainTagModel::TYPE_MORE);
    }

    public static function newModel()
    {
        $model    = static::$model;
        $instance = new $model;

        $instance->type = PlainTagModel::TYPE_MORE;

        return $instance;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}
