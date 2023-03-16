<?php

namespace App\Notifications\Supplier\Staff;

use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Models\Setting;
use App\Models\Staff;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SelectionSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - You have a new Bluon Member to be Verified ✨ Link: %s. Do Not Reply to this text.';
    protected Staff $staff;
    protected bool  $shouldSendSupplierSms;

    public function __construct(Staff $staff, bool $shouldSendSupplierSms)
    {
        $this->staff                 = $staff;
        $this->shouldSendSupplierSms = $shouldSendSupplierSms;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via              = [];
        $staff            = $this->staff;
        $configSmsEnabled = Config::get('notifications.sms.enabled');

        $slugStaffSmsSetting = Setting::SLUG_STAFF_SMS_NOTIFICATION;
        $shouldSendStaffSms  = App::make(GetNotificationSetting::class,
            ['staff' => $staff, 'slug' => $slugStaffSmsSetting])->execute();

        if ($staff->routeNotificationForTwilio() && $configSmsEnabled && $this->shouldSendSupplierSms && $shouldSendStaffSms) {
            $via[] = TwilioChannel::class;
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $accountUrl = Config::get('live.url') . Config::get('live.account.customers');

        return (new TwilioSmsMessage())->content(sprintf(self::SMS_TEXT, $accountUrl));
    }
}
