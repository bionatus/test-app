<?php

namespace App\Services;

use App\Constants\RelationsMorphs;
use App\Models\Media;
use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Series;
use App\Models\Tag;
use App\Scopes\Alphabetically;
use App\Scopes\ByBrandRouteKey;
use App\Scopes\ByType;
use App\Scopes\ModelType\BySeriesKey as ModelTypeBySeriesKey;
use App\Scopes\Scope;
use App\Types\TaggableType;
use Config;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class TaggableQuery
{
    protected Builder $query;
    private ?string   $typeFilter;
    private ?string   $parentFilter;

    public function __construct(string $typeFilter = null, string $parentFilter = null)
    {
        $this->typeFilter   = $typeFilter;
        $this->parentFilter = $parentFilter;

        $this->query = $this->buildQuery();

        $this->scoped(new Alphabetically());
        $this->scoped(new ByType($this->typeFilter));
    }

    private function buildQuery(): Builder
    {
        switch ($this->typeFilter) {
            case Series::MORPH_ALIAS:
                return DB::table($this->seriesQuery());
            case ModelType::MORPH_ALIAS:
                return DB::table($this->modelTypeQuery());
            default:
                $seriesQuery    = $this->seriesQuery();
                $modelTypeQuery = $this->modelTypeQuery();
                $plainTagQuery  = $this->plainTagsQuery();
                $final          = $plainTagQuery->unionAll($seriesQuery)->unionAll($modelTypeQuery);

                return DB::table($final);
        }
    }

    private function seriesQuery(): Builder
    {
        $query = DB::table(Series::tableName())
            ->join('brands', 'brand_id', '=', 'brands.id')
            ->select(['series.id'])
            ->selectRaw('CONCAT(CASE WHEN brands.name IS NULL THEN "" ELSE brands.name END, "|", CASE WHEN series.name IS NULL THEN "" ELSE series.name END) as name, "' . Series::MORPH_ALIAS . '" as type, series.id as model_key');

        if ($this->parentFilter) {
            $scope = new ByBrandRouteKey($this->parentFilter);
            $scope->apply($query);
        }

        return $query;
    }

    private function plainTagsQuery(): Builder
    {
        return DB::table(PlainTag::tableName())
            ->selectRaw(PlainTag::routeKeyName() . ' as id, name, type, id as model_key');
    }

    private function modelTypeQuery(): Builder
    {
        $query = DB::table(ModelType::tableName())
            ->selectRaw(ModelType::routeKeyName() . ' as id, name, "' . ModelType::MORPH_ALIAS . '" as type ,model_types.id as model_key');

        if ($this->parentFilter) {
            $scope = new ModelTypeBySeriesKey($this->parentFilter);
            $scope->apply($query);
        }

        return $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function scoped(Scope $scope): self
    {
        $scope->apply($this->query);

        return $this;
    }

    public function paginate($perPage): LengthAwarePaginator
    {
        if (!(filter_var($perPage, FILTER_VALIDATE_INT) !== false && (int) $perPage >= 0)) {
            $perPage = Config::get('pagination.per_page');
        }

        if (0 == $perPage) {
            $recordsCount = $this->query()->count();
            $perPage      = $recordsCount !== 0 ? $recordsCount : Config::get('pagination.per_page');
        }

        /** @var LengthAwarePaginator $page */
        $page = $this->query()->paginate(intval($perPage));

        $concatenatedTypeAndIds = Collection::make($page->items())->map(function($element) {
            $type = array_search(Tag::MORPH_MODEL_MAPS[$element->type], RelationsMorphs::MAP);

            return $element->model_key . '_' . $type;
        });

        $medias = Media::query()
            ->whereIn(DB::raw('CONCAT(model_id,\'_\',model_type)'), $concatenatedTypeAndIds->toArray())
            ->get();

        $page->through(function($item) use ($medias) {
            if ($media = $this->findMediaForItem($item, $medias)) {
                $item->media = $media;
            }

            return new TaggableType((array) $item);
        });

        return $page;
    }

    private function findMediaForItem($item, MediaCollection $medias): ?Media
    {
        return $medias->first(function(Media $media) use ($item) {
            $type = array_search(Tag::MORPH_MODEL_MAPS[$item->type], RelationsMorphs::MAP);

            return $media->model_type === $type && $media->model_id === $item->model_key;
        });
    }
}
