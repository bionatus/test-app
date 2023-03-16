<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook\Call;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Complete\StoreRequest;
use App\Jobs\LogCommunicationRequest;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CompleteController extends Controller
{
    private string $description = 'Success call/complete';

    public function store(StoreRequest $request)
    {
        $call = $request->call();
        $call->complete();
        LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), '', $request->route()->getName());

        return Response::noContent()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
