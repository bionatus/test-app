<?php

namespace App\Notifications\Supplier;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Mail\Supplier\SelectionEmail;
use App\Models\Setting;
use App\Models\Supplier;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SelectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - You have a new Bluon Member to be Verified ✨ Link: %s. Do Not Reply to this text.';
    protected Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via = [];

        $configSmsEnabled = Config::get('notifications.sms.enabled');

        $slugEmailSetting = Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL;
        $slugSmsSetting   = Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS;
        $shouldSendEmail  = App::make(GetNotificationSetting::class,
            ['supplier' => $this->supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSms    = App::make(GetNotificationSetting::class,
            ['supplier' => $this->supplier, 'slug' => $slugSmsSetting])->execute();
        if ($this->supplier->routeNotificationForTwilio() && $configSmsEnabled && $shouldSendSms) {
            $via[] = TwilioChannel::class;
        }

        if ($this->supplier->routeNotificationForTwilioByProkeepPhone() && $configSmsEnabled && $shouldSendSms) {
            $via[] = TwilioByProkeepPhoneChannel::class;
        }

        if ($this->supplier->hasContactEmail() && $shouldSendEmail) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $accountUrl = Config::get('live.url') . Config::get('live.account.customers');

        return (new TwilioSmsMessage())->content(sprintf(self::SMS_TEXT, $accountUrl));
    }

    public function toMail($notifiable)
    {
        return (new SelectionEmail($this->supplier))->to($notifiable->contact_email, $notifiable->name)
            ->bcc($notifiable->contact_secondary_email, $notifiable->name);
    }
}
