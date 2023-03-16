<?php

namespace App\Http\Controllers\Api\V4;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\CustomItem\InvokeRequest;
use App\Http\Resources\Api\V4\CustomItem\BaseResource;
use App\Models\Item;
use Auth;
use DB;
use Throwable;

class CustomItemController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(InvokeRequest $request)
    {
        $customItem = DB::transaction(function() use ($request) {
            $item = Item::create([
                'type' => Item::TYPE_CUSTOM_ITEM,
            ]);

            return Auth::user()->customItems()->create([
                'id'   => $item->getKey(),
                'name' => $request->get(RequestKeys::NAME),
            ]);
        });

        return new BaseResource($customItem);
    }
}
