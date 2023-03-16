<?php

namespace App\Http\Controllers\Api\V3\Point;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Point\XoxoVoucher\BaseResource;
use App\Http\Resources\Api\V3\Point\XoxoVoucher\DetailedResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\Published;
use App\Models\XoxoVoucher;

class XoxoVoucherController extends Controller
{
    public function index()
    {
        $xoxoVouchers = XoxoVoucher::scoped(new Published())
            ->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('id'))
            ->paginate();

        return BaseResource::collection($xoxoVouchers);
    }

    public function show(XoxoVoucher $xoxoVoucher)
    {
        return new DetailedResource($xoxoVoucher);
    }
}

