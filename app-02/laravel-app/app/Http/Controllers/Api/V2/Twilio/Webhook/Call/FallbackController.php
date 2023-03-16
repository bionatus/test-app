<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook\Call;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback\StoreRequest;
use App\Jobs\LogCommunicationRequest;
use App\Services\CustomerSupport\Call\ResponseInterface;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FallbackController extends Controller
{
    private string $description = 'Warning call/fallback';

    public function store(StoreRequest $request, ResponseInterface $providerResponse)
    {
        $call = $request->call();
        $call->complete();
        LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), '',
            $request->route()->getName());

        return Response::make($providerResponse->technicalDifficulties())->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
