<?php

namespace App\Http\Controllers\Api\V3;

use App;
use App\Actions\Models\IncrementPartSearches;
use App\Actions\Models\Part\SearchCharacterProximity;
use App\Actions\Models\User\IncrementPartViews;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Part\IndexRequest;
use App\Http\Resources\Api\V3\Part\BaseResource;
use App\Http\Resources\Api\V3\Part\DetailedResource;
use App\Models\Part;
use App\Models\Part\Scopes\FunctionalFirst;
use App\Models\Part\Scopes\MostViewed;
use App\Models\PartSearchCounter;
use App\Models\Scopes\OldestKey;
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
                new MostViewed(),
                new OldestKey(),
            ],
        ])->execute();

        /** @var App\Models\User $user */
        $user    = Auth::user();
        $results = $parts->total();

        $partSearchCounter = App::make(IncrementPartSearches::class, [
            'actor'    => $user,
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

        /** @var App\Models\User $user */
        $user = Auth::user();
        App::make(IncrementPartViews::class, [
            'user'              => $user,
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
                'string',
                Rule::exists(PartSearchCounter::tableName(), 'uuid')->whereNotNull('user_id'),
            ],
        ]);

        if ($validator->passes()) {
            return PartSearchCounter::firstWhere('uuid', $request->get(RequestKeys::PART_SEARCH_COUNTER));
        }

        return new PartSearchCounter();
    }
}

