<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\StoreRequest;
use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Services\CustomerSupport\Providers\Twilio\SupportCall\Response;
use Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CallController extends Controller
{
    use CanFetchAndReserveAgents;

    private string $description = 'Success call';

    /**
     * @throws Exception
     */
    public function store(StoreRequest $request, Response $providerResponse)
    {
        $source = $request->route()->getName();
        $call   = $this->createCall($request);

        $tech  = $request->tech();
        $agent = $this->fetchAndReserveAgent($call, $tech);

        $response = $agent ? $providerResponse->connect($call, $tech, $agent) : $providerResponse->retryAgainLater();

        LogCommunicationRequest::dispatch($call->communication, $this->description, $request->all(), $response,
            $source);

        return \Response::make($response)->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    /**
     * @throws Exception
     */
    private function createCall(StoreRequest $request): Call
    {
        $session = Session::create([
            'user_id'    => $request->tech()->getKey(),
            'subject_id' => $request->subject()->getKey(),
        ]);

        $communication = $session->communications()->create([
            'provider'    => $request->provider(),
            'provider_id' => $request->providerId(),
            'channel'     => Communication::CHANNEL_CALL,
        ]);

        return Call::create(['id' => $communication->getKey()]);
    }
}
