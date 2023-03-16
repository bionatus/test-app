<?php

namespace App\Http\Controllers\Api\V3\Auth\Phone\Register;

use App\Actions\Models\Term\GetCurrentTerm;
use App\Constants\RequestKeys;
use App\Events\User\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Assign\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Assign\BaseResource;
use App\Models\Phone;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;

class AssignController extends Controller
{
    public function __invoke(InvokeRequest $request, JWTAuth $auth)
    {
        /** @var Phone $phone */
        $phone = Auth::user();

        abort_if(!$phone->isVerified(), Response::HTTP_FORBIDDEN);

        $user                         = new User();
        $user->email                  = $request->get(RequestKeys::EMAIL);
        $user->password               = '';
        $user->phone                  = $phone->fullNumber();
        $user->registration_completed = true;
        $user->terms                  = true;
        $user->save();

        $currentTerm = App::make(GetCurrentTerm::class)->execute();

        if ($currentTerm) {
            $user->terms()->attach($currentTerm->getKey());
        }

        $user->refresh();

        $phone->user()->associate($user)->save();

        $token = $auth->fromUser($user);

        Created::dispatch($user);

        return new BaseResource($user, $token);
    }
}
