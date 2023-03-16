<?php

namespace App\Nova\Resources;

use App\Models\NoteCategory as NoteCategoryModel;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

/** @mixin NoteCategoryModel */
class NoteCategory extends Resource
{
    public static $model  = NoteCategoryModel::class;
    public static $title  = 'name';
    public static $search = [
        'id',
        'name',
    ];
    public static $group  = 'Current';

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Slug')->sortable(),
            Text::make('Name')->sortable()->rules('required', 'max:255'),
            HasMany::make('Notes', 'notes', Note::class)->hideFromIndex(),
        ];
    }

    public static function redirectAfterCreate(Request $request, $resource)
    {
        return '/resources/note-categories';
    }

    public static function redirectAfterUpdate(Request $request, $resource)
    {
        return '/resources/note-categories';
    }
}
