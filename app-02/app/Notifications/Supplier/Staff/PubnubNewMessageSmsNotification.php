<?php

namespace App\Notifications\Supplier\Staff;

use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Actions\Models\Supplier\GetSupplierRoutesUrl;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PubnubNewMessageSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const MESSAGE_LENGTH = 40;
    const SMS_TEXT       = 'BluonLive - You have a new message. %s from %s says: "%s". Do Not Reply to this text.';
    protected Supplier $supplier;
    protected User     $user;
    protected Staff    $staff;
    protected string   $message;
    protected bool     $shouldSendSupplierSms;
    private string     $linkUrl;

    public function __construct(
        Supplier $supplier,
        User $user,
        string $message,
        Staff $staff,
        bool $shouldSendSupplierSms
    ) {
        $this->supplier              = $supplier;
        $this->user                  = $user;
        $this->staff                 = $staff;
        $this->message               = $message;
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
        $message         = Str::limit($this->message, self::MESSAGE_LENGTH, '...');
        $smsTextReplaced = sprintf(self::SMS_TEXT, $this->user->fullName(), $this->user->companyName(), $message);

        return (new TwilioSmsMessage())->content($smsTextReplaced);
    }
}
