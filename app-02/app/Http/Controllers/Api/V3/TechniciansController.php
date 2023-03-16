<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Technician\BaseResource;
use App\Models\Technician;
use App\Models\Technicians\Scopes\ByShowInApp;

class TechniciansController extends Controller
{
    public function show()
    {
        $technicians = Technician::scoped(new ByShowInApp(true))->get();

        return BaseResource::collection($technicians);
    }
}
