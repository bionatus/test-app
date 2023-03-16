<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\CustomItem\InvokeRequest;
use App\Http\Resources\Api\V3\CustomItem\BaseResource;
use App\Models\CustomItem;
use App\Models\Item;
use Auth;
use DB;
use Throwable;

class CustomItemController extends Controller
{
    private CustomItem $customItem;

    /**
     * @throws Throwable
     */
    public function __invoke(InvokeRequest $request)
    {
        DB::transaction(function() use ($request) {
            $item = Item::create([
                'type' => Item::TYPE_CUSTOM_ITEM,
            ]);

            $this->customItem = Auth::user()->customItems()->create([
                'id'   => $item->getKey(),
                'name' => $request->get(RequestKeys::NAME),
            ]);
        });

        return new BaseResource($this->customItem);
    }
}
