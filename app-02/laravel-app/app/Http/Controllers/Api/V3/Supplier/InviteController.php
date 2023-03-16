<?php

namespace App\Http\Controllers\Api\V3\Supplier;

use App\Http\Controllers\Controller;
use App\Mail\Supplier\InviteEmail;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Auth;
use Illuminate\Support\Facades\Mail;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InviteController extends Controller
{
    public function __invoke(Supplier $supplier)
    {
        $user = Auth::user();

        Mail::to($supplier->email)->send(new InviteEmail($supplier));

        SupplierInvitation::create([
            'supplier_id' => $supplier->getKey(),
            'user_id'     => $user->getKey(),
        ]);

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}
