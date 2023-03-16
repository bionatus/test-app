<?php

namespace App\Http\Controllers\LiveApi\V1\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Supplier\BulkHour\StoreRequest;
use App\Http\Resources\LiveApi\V1\Supplier\BulkHour\BaseResource;
use App\Models\SupplierHour\Scopes\ExceptDays;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class BulkHourController extends Controller
{
    public function store(StoreRequest $request)
    {
        $supplier = Auth::user()->supplier;

        $rawHours    = Collection::make(is_array($request->get(RequestKeys::HOURS)) ? $request->get(RequestKeys::HOURS) : []);
        $branchHours = $rawHours->filter(function(array $hour) {
            return $hour['day'] && $hour['from'] && $hour['to'];
        });

        $supplier->supplierHours()->scoped(new ExceptDays($branchHours->pluck('day')))->delete();

        $branchHours->each(function(array $branchHourData) use ($supplier) {
            $branchHourData['from'] = Carbon::create($branchHourData['from'])->format('g:i a');
            $branchHourData['to']   = Carbon::create($branchHourData['to'])->format('g:i a');
            $supplier->supplierHours()->updateOrCreate(Arr::only($branchHourData, ['day']), $branchHourData);
        });

        return BaseResource::collection($supplier->supplierHours)->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
