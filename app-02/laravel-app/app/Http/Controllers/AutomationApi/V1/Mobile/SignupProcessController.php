<?php

namespace App\Http\Controllers\AutomationApi\V1\Mobile;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\AutomationApi\V1\Mobile\SignupProcess\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Phone;

class SignupProcessController extends Controller
{
    public function __invoke(Phone $phone)
    {
        /** @var AuthenticationCode $code */
        $code = $phone->authenticationCodes()->latest()->first();

        return new BaseResource($code);
    }
}
