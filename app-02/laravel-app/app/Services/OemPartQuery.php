<?php

namespace App\Services;

use App\Models\Oem;
use App\Models\OemDetailCounter;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\Scopes\ByKeys;
use App\Scopes\Scope;
use App\Types\RecentlyViewed;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class OemPartQuery
{
    protected Builder $query;
    private string    $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;

        $this->query = $this->buildQuery();
    }

    private function buildQuery(): Builder
    {
        $oemsQuery  = $this->oemsQuery();
        $partsQuery = $this->partsQuery();

        return $oemsQuery->unionAll($partsQuery)->orderByDesc('viewed_at');
    }

    private function oemsQuery(): Builder
    {
        return DB::table(OemDetailCounter::tableName())
            ->selectRaw('MAX(created_at) viewed_at, oem_id as object_id, "' . Oem::MORPH_ALIAS . '" as object_type')
            ->where('user_id', $this->userId)
            ->groupBy(['oem_id', 'user_id'])
            ->orderBy('viewed_at');
    }

    private function partsQuery(): Builder
    {
        return DB::table(PartDetailCounter::tableName())
            ->selectRaw('MAX(created_at) viewed_at, part_id as object_id, "' . Part::MORPH_ALIAS . '" as object_type')
            ->where('user_id', $this->userId)
            ->groupBy(['part_id', 'user_id'])
            ->orderBy('viewed_at');
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function paginate(): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator $page */
        $page = $this->query()->paginate();

        $oemsIds  = $page->where('object_type', Oem::MORPH_ALIAS)->pluck('object_id');
        $oemsList = Oem::query()->scoped(new ByKeys($oemsIds))->get();

        $partsIds  = $page->where('object_type', Part::MORPH_ALIAS)->pluck('object_id');
        $partsList = Part::query()->scoped(new ByKeys($partsIds))->get();

        $page->through(function($item) use ($oemsList, $partsList) {
            $recentlyViewed = new RecentlyViewed((array) $item);
            if ($item->object_type === Oem::MORPH_ALIAS) {
                $recentlyViewed->object = $oemsList->where('id', $item->object_id)->first();
            } elseif ($item->object_type === Part::MORPH_ALIAS) {
                $recentlyViewed->object = $partsList->where('id', $item->object_id)->first();
            }

            return $recentlyViewed;
        });

        return $page;
    }
}
