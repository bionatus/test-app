<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CreatePasswordNotification extends ResetPassword
{
    use Queueable;

    protected function buildMailMessage($url)
    {
        return (new MailMessage)->subject(Lang::get('Create New Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a create password request for your account.'))
            ->action(Lang::get('Create Password'), $url)
            ->line(Lang::get('This link will expire in :count minutes.', [
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]))
            ->line(Lang::get('If you did not request a password creation, no further action is required.'));
    }
}
