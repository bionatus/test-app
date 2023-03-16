<?php

namespace App\Http\Controllers\Api\V3\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Supplier\ChangeRequest\InvokeRequest;
use App\Mail\Supplier\ChangeRequestEmail;
use App\Models\Supplier;
use Auth;
use Config;
use Illuminate\Support\Facades\Mail;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ChangeRequestController extends Controller
{
    public function __invoke(InvokeRequest $invokeRequest, Supplier $supplier)
    {
        $user   = Auth::user();
        $reason = $invokeRequest->get(RequestKeys::REASON);
        $detail = $invokeRequest->get(RequestKeys::DETAIL);
        Mail::to(Config::get('mail.support.supplier.change'))->send(new ChangeRequestEmail($supplier, $user, $reason,
            $detail));

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
