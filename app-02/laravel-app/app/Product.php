<?php

namespace App;

use App\Models\IsTaggable;
use App\Models\Post;
use App\Models\Post\Scopes\ByTaggableTypes;
use App\Models\Series;
use App\Types\TaggableType;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'model',
        'brand',
        'fields',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'fields' => 'array',
    ];

    /**
     * Get the parsed project object
     *
     * @return Array
     */
    public function getParsedAttribute()
    {
        return [
            'model'       => $this->model,
            'fields'      => is_array($this->fields) ? $this->fields : json_decode($this->fields, true),
            'posts_count' => $this->getPostsCount(),
        ];
    }

    private function getPostsCount(): int
    {
        try {
            $series        = Series::find($this->series_id);
            $modelTypes    = $series ? $series->oems()
                ->with('modelType')
                ->get()
                ->pluck('modelType') : Collection::make();
            $taggableTypes = $modelTypes->prepend($series)->filter()->map(function(IsTaggable $taggable) {
                if (get_class($taggable) === System::class) {
                    return new TaggableType([
                        'id'        => $taggable->getRouteKey(),
                        'type'      => $taggable->morphType(),
                        'connector' => TaggableType::CONNECTOR_OR,
                    ]);
                }

                return $taggable->toTagType();
            });

            return Post::scoped(new ByTaggableTypes($taggableTypes))->count();
        } catch (Exception $e) {
            return 0;
        }
    }
}
