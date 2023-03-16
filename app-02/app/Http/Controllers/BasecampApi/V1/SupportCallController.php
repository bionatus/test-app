<?php

namespace App\Http\Controllers\BasecampApi\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BasecampApi\V1\SupportCall\BaseResource;
use App\Models\SupportCall;

class SupportCallController extends Controller
{
    public function show(SupportCall $supportCall)
    {
        return new BaseResource($supportCall);
    }
}
