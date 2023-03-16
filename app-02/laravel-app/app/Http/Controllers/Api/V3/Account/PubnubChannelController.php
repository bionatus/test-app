<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\PubnubChannel\BaseResource;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByUser;
use Auth;

class PubnubChannelController extends Controller
{
    public function index()
    {
        $user           = Auth::user();
        $pubnubChannels = PubnubChannel::scoped(new ByUser($user))->get();

        return BaseResource::collection($pubnubChannels);
    }
}
