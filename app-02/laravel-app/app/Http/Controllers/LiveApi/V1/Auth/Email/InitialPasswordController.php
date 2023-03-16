<?php

namespace App\Http\Controllers\LiveApi\V1\Auth\Email;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InitialPasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        $staff                          = Auth::user();
        $staff->password                = Hash::make($request->get(RequestKeys::PASSWORD));
        $staff->initial_password_set_at ??= Carbon::now();
        $staff->tos_accepted_at         ??= Carbon::now();
        $staff->save();

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
