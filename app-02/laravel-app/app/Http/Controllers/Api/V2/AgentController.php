<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Agent\BaseResource;
use App\Models\Agent;
use App\Models\Agent\Scopes\Oldest;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $page = Agent::scoped(new Oldest())->paginate();
        $page->appends($request->all());

        return BaseResource::collection($page);
    }
}
