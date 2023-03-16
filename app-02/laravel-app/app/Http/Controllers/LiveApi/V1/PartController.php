<?php

namespace App\Http\Controllers\LiveApi\V1;

use App;
use App\Actions\Models\IncrementPartSearches;
use App\Actions\Models\Part\SearchCharacterProximity;
use App\Actions\Models\Staff\IncrementPartViews;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Part\IndexRequest;
use App\Http\Resources\LiveApi\V1\Part\BaseResource;
use App\Http\Resources\LiveApi\V1\Part\DetailedResource;
use App\Models\Part;
use App\Models\Part\Scopes\FunctionalFirst;
use App\Models\PartSearchCounter;
use App\Models\Scopes\OldestKey;
use App\Models\Staff;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PartController extends Controller
{
    public function index(IndexRequest $request)
    {
        $newSearchString = $request->get(RequestKeys::NUMBER);
        $repeat          = 0;
        $maxSearch       = 10;
        $minCharacter    = 3;

        [$parts, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $newSearchString,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                new FunctionalFirst(),
                new OldestKey(),
            ],
        ])->execute();

        /** @var Staff $staff */
        $staff   = Auth::user();
        $results = $parts->total();

        $partSearchCounter = App::make(IncrementPartSearches::class, [
            'actor'    => $staff,
            'criteria' => $newSearchString,
            'results'  => $results,
        ])->execute();

        return BaseResource::collection($parts)->additional([
            'meta' => [
                'search_term'            => $searchTerm,
                'part_search_counter_id' => $partSearchCounter->uuid,
            ],
        ]);
    }

    public function show(Request $request, Part $part)
    {
        $partSearchCounter = $this->getPartSearchCounter($request);

        /** @var Staff $staff */
        $staff = Auth::user();
        App::make(IncrementPartViews::class, [
            'staff'             => $staff,
            'part'              => $part,
            'partSearchCounter' => $partSearchCounter,
        ])->execute();

        return new DetailedResource($part);
    }

    private function getPartSearchCounter(Request $request): PartSearchCounter
    {
        $validator = Validator::make($request->query(), [
            RequestKeys::PART_SEARCH_COUNTER => [
                'required',
                Rule::exists(PartSearchCounter::tableName(), 'uuid')->whereNotNull('staff_id'),
            ],
        ]);

        if ($validator->passes()) {
            return PartSearchCounter::firstWhere('uuid', $request->get(RequestKeys::PART_SEARCH_COUNTER));
        }

        return new PartSearchCounter();
    }
}
