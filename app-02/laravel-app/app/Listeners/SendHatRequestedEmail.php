<?php

namespace App\Listeners;

use App\Events\User\HatRequested;
use App\Mail\HatRequestedEmail;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;

class SendHatRequestedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public $connection = 'database';

    public function handle(HatRequested $event)
    {
        Mail::to(Config::get('mail.support.hat'))->send(new HatRequestedEmail($event->user()));
    }
}
