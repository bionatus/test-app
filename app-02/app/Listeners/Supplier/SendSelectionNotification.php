<?php

namespace App\Listeners\Supplier;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Supplier\Selected as SelectedEvent;
use App\Models\Setting;
use App\Notifications\Supplier\SelectionNotification as SelectionNotificationToSupplier;
use App\Notifications\Supplier\Staff\SelectionEmailNotification as SelectionEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\SelectionSmsNotification as SelectionSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSelectionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SelectedEvent $event)
    {
        $supplier = $event->supplier();

        $supplier->notify(new SelectionNotificationToSupplier($supplier));

        $slugEmailSetting        = Setting::SLUG_NEW_MEMBER_NOTIFICATION_EMAIL;
        $slugSmsSetting          = Setting::SLUG_NEW_MEMBER_NOTIFICATION_SMS;
        $shouldSendSupplierEmail = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSupplierSms   = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();
        foreach ($supplier->counters as $counterStaff) {
            $counterStaff->notify(new SelectionEmailNotificationToStaff($counterStaff, $supplier,
                $shouldSendSupplierEmail));
            $counterStaff->notify(new SelectionSmsNotificationToStaff($counterStaff, $shouldSendSupplierSms));
        }
    }
}
