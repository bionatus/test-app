<?php

namespace App\Http\Controllers\LiveApi\V1;

use App;
use App\Actions\Models\IncrementOemSearches;
use App\Actions\Models\Oem\SearchCharacterProximity;
use App\Actions\Models\Staff\IncrementOemViews;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Oem\IndexRequest;
use App\Http\Resources\LiveApi\V1\Oem\BaseResource;
use App\Http\Resources\LiveApi\V1\Oem\DetailedResource;
use App\Models\Oem;
use App\Models\Oem\Scopes\Alphabetically;
use App\Models\OemSearchCounter;
use App\Models\Scopes\OldestKey;
use App\Models\Staff;
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
                new Alphabetically('model'),
                new Alphabetically('model_notes'),
                new OldestKey(),
            ],
        ])->execute();

        /** @var Staff $staff */
        $staff   = Auth::user();
        $results = $oems->total();

        $oemSearchCounter = App::make(IncrementOemSearches::class, [
            'actor'    => $staff,
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

    public function show(Request $request, Oem $oem)
    {
        $oemSearchCounter = $this->getOemSearchCounter($request);

        /** @var Staff $staff */
        $staff = Auth::user();
        App::make(IncrementOemViews::class, [
            'staff'            => $staff,
            'oem'              => $oem,
            'oemSearchCounter' => $oemSearchCounter,
        ])->execute();

        return new DetailedResource($oem);
    }

    private function getOemSearchCounter(Request $request): OemSearchCounter
    {
        $validator = Validator::make($request->query(), [
            RequestKeys::OEM_SEARCH_COUNTER => [
                'required',
                Rule::exists(OemSearchCounter::tableName(), 'uuid')->whereNotNull('staff_id'),
            ],
        ]);

        if ($validator->passes()) {
            return OemSearchCounter::firstWhere('uuid', $request->get(RequestKeys::OEM_SEARCH_COUNTER));
        }

        return new OemSearchCounter();
    }
}
