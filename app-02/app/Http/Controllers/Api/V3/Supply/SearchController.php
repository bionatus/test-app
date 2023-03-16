<?php

namespace App\Http\Controllers\Api\V3\Supply;

use App;
use App\Actions\Models\IncrementSupplySearches;
use App\Actions\Models\Supply\SearchCharacterProximity;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Supply\Search\InvokeRequest;
use App\Http\Resources\Api\V3\Supply\Search\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Supply\Scopes\MostAddedToCart;
use Auth;

class SearchController extends Controller
{
    const REPEAT        = 0;
    const MAX_SEARCH    = 10;
    const MIN_CHARACTER = 3;

    public function __invoke(InvokeRequest $request)
    {
        $newSearchString = $request->get(RequestKeys::NAME);

        [$supplies, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $newSearchString,
            'repeat'          => self::REPEAT,
            'maxSearch'       => self::MAX_SEARCH,
            'minCharacter'    => self::MIN_CHARACTER,
            'orderScopes'     => [
                new MostAddedToCart(),
                new Alphabetically(),
            ],
        ])->execute();

        /** @var App\Models\User $user */
        $user = Auth::user();

        App::make(IncrementSupplySearches::class, [
            'actor'    => $user,
            'criteria' => $newSearchString,
            'results'  => $supplies->total(),
        ])->execute();

        return BaseResource::collection($supplies)->additional([
            'meta' => [
                'search_term' => $searchTerm,
            ],
        ]);
    }
}
