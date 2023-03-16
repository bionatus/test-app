<?php

namespace App\Http\Controllers\LiveApi\V1\Part;

use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Part\RecommendedReplacement\StoreRequest;
use App\Http\Resources\LiveApi\V1\Part\RecommendedReplacement\BaseResource;
use App\Models\Part;
use App\Models\RecommendedReplacement;
use Auth;

class RecommendedReplacementController extends Controller
{
    public function store(StoreRequest $request, Part $part)
    {
        $user     = Auth::user();
        $supplier = $user->supplier;

        /** @var RecommendedReplacement $recommendedReplacement */
        $recommendedReplacement = $supplier->recommendedReplacements()->create([
            'original_part_id' => $part->getKey(),
            'brand'            => $request->get('brand'),
            'part_number'      => $request->get('part_number'),
            'note'             => $request->get('note'),
        ]);

        return new BaseResource($recommendedReplacement);
    }
}
