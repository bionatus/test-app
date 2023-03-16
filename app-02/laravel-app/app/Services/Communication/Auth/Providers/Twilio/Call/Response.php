<?php

namespace App\Services\Communication\Auth\Providers\Twilio\Call;

use App\Services\Communication\Auth\Call\ResponseInterface;
use Twilio\TwiML\VoiceResponse;

class Response implements ResponseInterface
{
    public function sayCode(string $code): string
    {
        $response = new VoiceResponse();

        $response->pause()->setLength(1);
        $response->say("Your bluon authentication code is:");
        $response->pause()->setLength(1);
        $response->say($code);
        $response->pause()->setLength(1);
        $response->say('Good bye.');

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

        $message = 'We are very sorry, currently we are experiencing technical difficulties. Please contact us at a later time.';

        $response->say($message);

        return $response->asXML();
    }
}
