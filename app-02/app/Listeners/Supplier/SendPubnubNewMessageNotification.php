<?php

namespace App\Listeners\Supplier;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Supplier\NewMessage;
use App\Models\Setting;
use App\Notifications\Supplier\PubnubNewMessageNotification as PubnubChannelNewMessageNotificationToSupplier;
use App\Notifications\Supplier\Staff\PubnubNewMessageEmailNotification as PubnubNewMessageEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\PubnubNewMessageSmsNotification as PubnubNewMessageSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPubnubNewMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NewMessage $event)
    {
        $supplier = $event->supplier();
        $user     = $event->user();
        $message  = $event->message();

        $supplier->notify(new PubnubChannelNewMessageNotificationToSupplier($supplier, $user, $message));

        $slugEmailSetting        = Setting::SLUG_NEW_MESSAGE_NOTIFICATION_EMAIL;
        $slugSmsSetting          = Setting::SLUG_NEW_MESSAGE_NOTIFICATION_SMS;
        $shouldSendSupplierEmail = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSupplierSms   = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();
        foreach ($supplier->counters as $counterStaff) {
            $counterStaff->notify(new PubnubNewMessageEmailNotificationToStaff($supplier, $user, $message,
                $counterStaff, $shouldSendSupplierEmail));
            $counterStaff->notify(new PubnubNewMessageSmsNotificationToStaff($supplier, $user, $message,
                $counterStaff, $shouldSendSupplierSms));
        }
    }
}
