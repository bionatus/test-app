<?php

namespace App\Jobs;

use App\Models\Phone;
use App\Services\Twilio\RestClient;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Exceptions\TwilioException;

class PhoneAuthenticationCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Phone $phone;

    public function __construct(Phone $phone)
    {
        $this->phone = $phone;
        $this->onConnection('sync');
    }

    /**
     * @throws TwilioException
     */
    public function handle(RestClient $client)
    {
        $calls = $client->calls;

        return $calls->create('+' . $this->phone->fullNumber(), '+' . Config::get('twilio.numbers.auth'), [
            'ApplicationSid' => Config::get('twilio.app_sids.auth'),
        ]);
    }
}
