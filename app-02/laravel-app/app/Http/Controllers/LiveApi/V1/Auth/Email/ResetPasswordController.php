<?php

namespace App\Http\Controllers\LiveApi\V1\Auth\Email;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Auth\Email\ResetPassword\StoreRequest;
use App\Models\Staff;
use Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Lang;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ResetPasswordController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
    {
        $credentials = array_merge(['type' => Staff::TYPE_OWNER], $request->validated());

        $status = Password::broker('live')->reset($credentials, function(Staff $staff) use ($request) {
            $staff->password = Hash::make($request->get(RequestKeys::PASSWORD));
            $staff->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                RequestKeys::EMAIL => 'The password reset token and email combination is invalid.',
            ]);
        }

        return Response::json(['message' => Lang::get($status)], HttpResponse::HTTP_CREATED);
    }
}
