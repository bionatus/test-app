<?php

namespace App\Http\Controllers\LiveApi\V2\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Supplier\Staff\IndexRequest;
use App\Http\Resources\LiveApi\V2\Supplier\Staff\BaseResource;
use App\Models\Scopes\ByType;
use App\Models\Scopes\Oldest;
use App\Models\Staff\Scopes\LastAssigned;
use Auth;
use Illuminate\Support\Collection;

class StaffController extends Controller
{
    public function index(IndexRequest $request)
    {
        $supplier  = Auth::user()->supplier;
        $validated = Collection::make($request->validated());

        $supplierStaff = $supplier->staff();
        if ($staffType = $request->get(RequestKeys::TYPE)) {
            $supplierStaff->scoped(new ByType($staffType));
        }
        $page = $supplierStaff->scoped(new LastAssigned())->scoped(new Oldest())->paginate();
        $page->appends($validated->toArray());

        return BaseResource::collection($page);
    }
}
