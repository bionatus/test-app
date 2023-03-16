<?php

namespace App\Nova\Resources;

use App\Constants\MediaCollectionNames;
use App\Constants\MediaConversionNames;
use App\Models\Note as NoteModel;
use App\Models\NoteCategory as NoteCategoryModel;
use App\Models\Scopes\ByRouteKey;
use App\Nova\Resource;
use Config;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @mixin NoteModel */
class Note extends Resource
{
    public static $model  = NoteModel::class;
    public static $title  = 'title';
    public static $search = [
        'id',
        'slug',
        'title',
        'body',
        'link',
        'link_text',
    ];
    public static $group  = 'Current';

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Title')->sortable()->rules('required', 'max:26'),
            Text::make('Slug')->sortable()->readonly()->hideWhenCreating()->hideWhenUpdating(),
            BelongsTo::make(__('Note Category'), 'noteCategory', NoteCategory::class)
                ->sortable()
                ->rules(['required'])
                ->readonly(function() {
                    $flag = false;
                    if ($this->resource->noteCategory()->exists()) {
                        $flag = $this->resource->noteCategory()->first()->slug == NoteCategoryModel::SLUG_GAMIFICATION;
                    }

                    return $flag;
                }),
            Images::make('Image', MediaCollectionNames::IMAGES)
                ->croppable(false)
                ->conversionOnIndexView(MediaConversionNames::THUMB)
                ->conversionOnForm(MediaConversionNames::THUMB)
                ->conversionOnPreview(MediaConversionNames::THUMB)
                ->conversionOnDetailView(MediaConversionNames::THUMB)
                ->singleMediaRules(['mimes:jpg,jpeg,png,gif'])
                ->setMaxFileSize(Config::get('media-library.max_file_size') / 1024),
            Text::make('Body')->rules('required', 'max:90')->hideFromIndex(),
            Text::make('Link')->rules('nullable', 'max:255', 'url')->hideFromIndex(),
            Text::make('Link Text')->rules('max:255')->hideFromIndex(),
            Number::make(__('Sort'), 'sort')->sortable()->rules(['nullable', 'integer', 'min:1']),
        ];
    }

    public static function relatableNoteCategories(NovaRequest $request, $query)
    {
        return $query->scoped(new ByRouteKey(NoteCategoryModel::SLUG_FEATURED));
    }

    protected static function afterCreationValidation(NovaRequest $request, $validator)
    {
        $noteCategory = NoteCategoryModel::scoped(new ByRouteKey(NoteCategoryModel::SLUG_FEATURED))->first();
        if (NoteModel::where('note_category_id', $noteCategory->getKey())->count() >= 5) {
            $validator->errors()->add('noteCategory', 'You can not create more than 5 featured notes');
        }
    }

    public static function redirectAfterCreate(Request $request, $resource)
    {
        return '/resources/notes';
    }

    public static function redirectAfterUpdate(Request $request, $resource)
    {
        return '/resources/notes';
    }
}
