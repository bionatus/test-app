<?php

namespace App\Http\Controllers\Api\V3\Account\Term;

use App;
use App\Actions\Models\Term\GetCurrentTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Term\Accept\InvokeRequest;
use App\Http\Resources\Api\V3\Account\Term\BaseResource;
use App\Models\Scopes\ByUser;
use Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AcceptController extends Controller
{
    public function __invoke(InvokeRequest $request)
    {
        $user = Auth::user();

        $currentTerm = App::make(GetCurrentTerm::class)->execute();
        $termUser    = $currentTerm->termUsers()->scoped(new ByUser($user))->firstOrCreate([
            'user_id' => $user->getKey(),
        ]);

        return (new BaseResource($termUser))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
