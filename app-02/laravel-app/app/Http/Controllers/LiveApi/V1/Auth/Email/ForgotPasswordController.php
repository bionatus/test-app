<?php

namespace App\Http\Controllers\LiveApi\V1\Auth\Email;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Auth\Email\ForgotPassword\StoreRequest;
use App\Models\Staff;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Lang;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ForgotPasswordController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
    {
        $credentials = [
            'email' => $request->only('email'),
            'type'  => Staff::TYPE_OWNER,
        ];
        $status      = Password::broker('live')->sendResetLink($credentials);

        if (Password::RESET_LINK_SENT !== $status && Password::INVALID_USER !== $status) {
            throw ValidationException::withMessages([
                RequestKeys::EMAIL => Lang::get($status),
            ]);
        }

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
