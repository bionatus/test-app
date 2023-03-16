<?php

namespace App\Notifications\LiveApi\V1;

use Arr;
use Config;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    public function toMail($notifiable)
    {
        $baseLiveUrl   = Config::get('live.url');
        $passwordReset = Config::get('live.routes.password_reset');
        $url           = Str::replace('{token}', $this->token, $baseLiveUrl . $passwordReset);

        $fullUrl = $url . '?' . Arr::query(['email' => $notifiable->getEmailForPasswordReset()]);

        return $this->buildMailMessage($fullUrl)->markdown('notifications.live-api.v1.email');
    }
}
