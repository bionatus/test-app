<?php

namespace App\Nova\Resources;

use App\Models\Term as TermModel;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

/** @mixin TermModel */
class Term extends Resource
{
    public static $model  = TermModel::class;
    public static $title  = 'title';
    public static $search = [
        'id',
        'title',
        'link',
        'required_at',
    ];
    public static $group  = 'Current';

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Title')->sortable()->rules('required', 'max:26'),
            Textarea::make('Body')->rules('required')->hideFromIndex(),
            Text::make('Link')->sortable()->rules('required', 'url', 'max:255')->hideFromIndex(),
            Date::make('Required At')->sortable()->rules('required'),
        ];
    }
}
