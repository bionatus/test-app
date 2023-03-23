<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook\Call;

use App;
use App\Http\Controllers\Api\V2\Twilio\Webhook\CanFetchAndReserveAgents;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action\StoreRequest;
use App\Jobs\LogCommunicationRequest;
use App\Services\CustomerSupport\Providers\Twilio\SupportCall\Response;
use Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ActionController extends Controller
{
    use CanFetchAndReserveAgents;

    private string $description = 'Success call/action';

    /**
     * @throws Exception
     */
    public function store(StoreRequest $request, Response $providerResponse)
    {
        $call   = $request->call();
        $source = $request->route()->getName();
        if ($request->callEnded()) {
            $call->complete();
            $response = $providerResponse->hangUp();
            LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), $response,
                $source);

            return \Response::make($response)->setStatusCode(SymfonyResponse::HTTP_CREATED);
        }

        if ($request->agentHungUp()) {
            $call->complete();
            $response = $providerResponse->thanksForCalling();
            LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), $response, $source);

            return \Response::make($response)->setStatusCode(SymfonyResponse::HTTP_CREATED);
        }

        $tech  = $call->communication->session->user;
        $agent = $this->fetchAndReserveAgent($call, $tech);

        $response = $agent ? $providerResponse->connect($call, $tech, $agent) : $providerResponse->retryAgainLater();

        LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), $response, $source);

        return \Response::make($response)->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
