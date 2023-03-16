<?php

namespace App\Notifications\Supplier;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Actions\Models\Supplier\GetSupplierRoutesUrl;
use App\Mail\Supplier\NewMessageEmail;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PubnubNewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const MESSAGE_LENGTH = 40;
    const SMS_TEXT       = 'BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.';
    protected Supplier $supplier;
    protected User     $user;
    protected string   $message;
    private string     $linkUrl;

    public function __construct(Supplier $supplier, User $user, string $message)
    {
        $this->supplier = $supplier;
        $this->user     = $user;
        $this->message  = $message;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via = [];

        $configSmsEnabled = Config::get('notifications.sms.enabled');

        $slugEmailSetting = Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL;
        $slugSmsSetting   = Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS;
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
        $message         = Str::limit($this->message, self::MESSAGE_LENGTH, '...');
        $smsTextReplaced = sprintf(self::SMS_TEXT, $this->user->fullName(), $this->user->companyName(), $message);

        return (new TwilioSmsMessage())->content($smsTextReplaced);
    }

    public function toMail($notifiable)
    {
        $linkUrl = App::make(GetSupplierRoutesUrl::class, ['supplier' => $this->supplier, 'user' => $this->user])
            ->execute();

        return (new NewMessageEmail($notifiable, $this->user, $this->message, $linkUrl))->to($notifiable->contact_email,
            $notifiable->name)->bcc($notifiable->contact_secondary_email, $notifiable->name);
    }
}
