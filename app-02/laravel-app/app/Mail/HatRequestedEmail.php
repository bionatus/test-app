<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HatRequestedEmail extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.support.hat-requested', ['user' => $this->user])->subject('Hat requested');
    }
}
