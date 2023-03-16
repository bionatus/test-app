<?php

namespace App\Http\Controllers\Api\V2\Twilio\Webhook\Call\Client;

use App;
use App\Events\AgentCall\Answered;
use App\Events\AgentCall\Ringing;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status\StoreRequest;
use App\Jobs\DelayUnsolvedTicketNotification;
use App\Jobs\LogCommunicationRequest;
use App\Models\Ticket;
use Event;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class StatusController extends Controller
{
    private string $description = 'Success call/client/status';

    public function store(StoreRequest $request)
    {
        $agentCall = $request->agentCall();

        $agentCall->status = $request->status();
        $agentCall->save();

        $call = $agentCall->call;

        $session = $call->communication->session;

        LogCommunicationRequest::dispatch($agentCall->call->communication, $this->description, $request->all(), '',
            $request->route()->getName());

        if ($agentCall->isRinging()) {
            Event::dispatch(new Ringing($agentCall->refresh()));
        }

        if ($agentCall->isInProgress()) {
            $session->ticket()->associate(Ticket::create([
                'user_id'    => $session->user->getKey(),
                'subject_id' => $session->subject->getKey(),
                'topic'      => $session->subject->name,
            ]));
            $session->save();

            Event::dispatch(new Answered($agentCall->refresh()));
        }

        if ($agentCall->isCompleted()) {
            if ($session->ticket) {
                DelayUnsolvedTicketNotification::dispatch($agentCall);
            }
        }

        return Response::noContent()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
