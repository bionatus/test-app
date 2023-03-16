<?php

namespace App\Http\Controllers\Api\V3;

use App;
use App\Actions\Models\IncrementOemSearches;
use App\Actions\Models\Oem\SearchCharacterProximity;
use App\Actions\Models\User\IncrementOemViews;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Oem\IndexRequest;
use App\Http\Requests\Api\V3\Oem\ShowRequest;
use App\Http\Resources\Api\V3\Oem\BaseResource;
use App\Http\Resources\Api\V3\Oem\DetailedResource;
use App\Models\Layout;
use App\Models\Layout\Scopes\ByVersion;
use App\Models\Layout\Scopes\Highest;
use App\Models\Oem;
use App\Models\Oem\Scopes\Alphabetically;
use App\Models\Oem\Scopes\MostViewed;
use App\Models\OemSearchCounter;
use App\Models\Scopes\OldestKey;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OemController extends Controller
{
    public function index(IndexRequest $request)
    {
        $newSearchString = $request->get(RequestKeys::MODEL);
        $repeat          = 0;
        $maxSearch       = 10;
        $minCharacter    = 3;

        [$oems, $searchTerm] = App::make(SearchCharacterProximity::class, [
            'newSearchString' => $newSearchString,
            'repeat'          => $repeat,
            'maxSearch'       => $maxSearch,
            'minCharacter'    => $minCharacter,
            'orderScopes'     => [
                new MostViewed(),
                new Alphabetically('model'),
                new Alphabetically('model_notes'),
                new OldestKey(),
            ],
        ])->execute();

        /** @var App\Models\User $user */
        $user    = Auth::user();
        $results = $oems->total();

        $oemSearchCounter = App::make(IncrementOemSearches::class, [
            'actor'    => $user,
            'criteria' => $newSearchString,
            'results'  => $results,
        ])->execute();

        return BaseResource::collection($oems)->additional([
            'meta' => [
                'search_term'           => $searchTerm,
                'oem_search_counter_id' => $oemSearchCounter->uuid,
            ],
        ]);
    }

    public function show(ShowRequest $request, Oem $oem)
    {
        $oemSearchCounter = $this->getOemSearchCounter($request);

        /** @var Layout $layout */
        $layout = Layout::query()
            ->scoped(new ByVersion($request->get(RequestKeys::VERSION)))
            ->scoped(new Highest())
            ->firstOrFail();

        /** @var App\Models\User $user */
        $user = Auth::user();
        App::make(IncrementOemViews::class, [
            'user'             => $user,
            'oem'              => $oem,
            'oemSearchCounter' => $oemSearchCounter,
        ])->execute();

        return new DetailedResource($oem, $layout, $user);
    }

    private function getOemSearchCounter(Request $request): OemSearchCounter
    {
        $validator = Validator::make($request->query(), [
            RequestKeys::OEM_SEARCH_COUNTER => [
                'required',
                'string',
                Rule::exists(OemSearchCounter::tableName(), 'uuid')->whereNotNull('user_id'),
            ],
        ]);

        if ($validator->passes()) {
            return OemSearchCounter::firstWhere('uuid', $request->get(RequestKeys::OEM_SEARCH_COUNTER));
        }

        return new OemSearchCounter();
    }
}
