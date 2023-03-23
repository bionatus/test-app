<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Models\Instrument as InstrumentModel;
use App\Nova\Resource;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Instrument extends Resource
{
    public static $model  = InstrumentModel::class;
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
            Images::make(__('Image'), MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
            BelongsToMany::make(__('Support Call Categories'), 'supportCallCategories'),
        ];
    }
}
