<?php

namespace App\Http\Controllers\LiveApi\V2;

use App;
use App\Actions\Models\Staff\IncrementPartViews;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V2\Part\DetailedResource;
use App\Models\Part;
use App\Models\PartSearchCounter;
use App\Models\Staff;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class PartController extends Controller
{
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
