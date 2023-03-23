<?php

namespace App\Http\Controllers\Api\V3\Auth;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Auth\CreatePassword\StoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Lang;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CreatePasswordController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
    {
        $status = Password::broker('latam')->sendResetLink($request->only('email'), function(User $user, $token) {
            $user->sendCreatePasswordNotification($token);
        });

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                RequestKeys::EMAIL => Lang::get($status),
            ]);
        }

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
