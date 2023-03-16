<?php

namespace App\Http\Controllers\Api\V2\Support\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Support\Ticket\Close\BaseResource;
use App\Models\Ticket;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CloseController extends Controller
{
    public function store(Ticket $ticket)
    {
        $ticket->close();

        return (new BaseResource($ticket))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
