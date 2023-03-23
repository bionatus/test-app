<?php

namespace App\Services\CustomerSupport\Providers\Twilio\SupportCall;

use App\Constants\RouteNames;
use App\Models\Agent;
use App\Models\Call;
use App\Models\User;
use App\Services\CustomerSupport\Call\ResponseInterface;
use Config;
use Storage;
use Twilio\TwiML\VoiceResponse;
use URL;

class Response implements ResponseInterface
{
    private string $thanksMessage                = 'Thank you for calling Bluon support.';
    private string $retryMessage                 = "We couldn't find an available agent. Please wait a few minutes and try again.";
    private string $greetingMessage              = 'Welcome to Bluon support. An agent will be with you shortly. Please wait a moment.';
    private string $technicalDifficultiesMessage = 'We are very sorry, currently we are experiencing technical difficulties. Please contact us at a later time.';

    public function retryAgainLater(): string
    {
        $response = new VoiceResponse();

        $response->say($this->retryMessage)->asXML();

        return $response;
    }

    public function thanksForCalling(): string
    {
        $response = new VoiceResponse();

        $response->say($this->thanksMessage);

        return $response->asXML();
    }

    public function connect(Call $call, User $user, Agent $agent): string
    {
        $response = new VoiceResponse();
        if ($call->wasRecentlyCreated) {
            $response->pause()->setLength(1);
            $response->say($this->greetingMessage);
            $response->pause()->setLength(1);
        }

        $dial = $response->dial();
        $dial->setCallerId($user->getKey());
        $dial->setTimeLimit(Config::get('communications.calls.max_duration'));
        $dial->setAnswerOnBridge(true);
        $dial->setTimeout(Config::get('communications.calls.agent_ringing_time'));
        $dial->setAction(URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_ACTION_STORE));

        $client = $dial->client();
        $client->identity($agent->getKey());
        $subject = $call->communication->session->subject;
        $data    = [
            'user'  => [
                'id'    => $user->getRouteKey(),
                'name'  => $user->name ?: ($user->first_name . ' ' . $user->last_name),
                'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : null,
            ],
            'topic' => [
                'id'   => $subject->getRouteKey(),
                'name' => $subject->isTopic() ? $subject->name : ($subject->subtopic->topic->subject->name . '/' . $subject->name),
            ],
        ];
        $client->parameter(['name' => 'data', 'value' => json_encode($data)]);
        $client->setStatusCallback(URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE));
        $statusCallbacks = implode(' ', [
            Call::TWILIO_STATUS_CALLBACK_RINGING,
            Call::TWILIO_STATUS_CALLBACK_ANSWERED,
            Call::TWILIO_STATUS_CALLBACK_COMPLETED,
        ]);
        $client->setStatusCallbackEvent($statusCallbacks);

        return $response->asXML();
    }

    public function hangUp(): string
    {
        $response = new VoiceResponse();

        $response->hangup();

        return $response->asXML();
    }

    public function technicalDifficulties(): string
    {
        $response = new VoiceResponse();

        $response->say($this->technicalDifficultiesMessage);

        return $response->asXML();
    }
}
