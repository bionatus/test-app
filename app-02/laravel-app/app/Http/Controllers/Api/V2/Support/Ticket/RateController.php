<?php

namespace App\Http\Controllers\Api\V2\Support\Ticket;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Support\Ticket\Rate\StoreRequest;
use App\Http\Resources\Api\V2\Support\Ticket\Rate\BaseResource;
use App\Models\Ticket;
use Symfony\Component\HttpFoundation\Response;

class RateController extends Controller
{
    public function store(StoreRequest $request, Ticket $ticket)
    {
        $ticket->rating  = $request->get(RequestKeys::RATING);
        $ticket->comment = $request->get(RequestKeys::COMMENT);
        $ticket->save();

        return (new BaseResource($ticket))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
