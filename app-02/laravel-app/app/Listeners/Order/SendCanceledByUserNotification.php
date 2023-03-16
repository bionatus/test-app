<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Order\CanceledByUser;
use App\Models\Setting;
use App\Notifications\Supplier\OrderCanceledByUserNotification as OrderCanceledByUserNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderCanceledByUserEmailNotification as OrderCanceledByUserEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderCanceledByUserSmsNotification as OrderCanceledByUserSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCanceledByUserNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CanceledByUser $event)
    {
        $order    = $event->order();
        $supplier = $order->supplier;

        $supplier->notify(new OrderCanceledByUserNotificationToSupplier($order));

        $slugEmailSetting        = Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL;
        $slugSmsSetting          = Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS;
        $shouldSendSupplierEmail = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSupplierSms   = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();

        foreach ($supplier->counters as $counterStaff) {
            $counterStaff->notify(new OrderCanceledByUserEmailNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierEmail));
            $counterStaff->notify(new OrderCanceledByUserSmsNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierSms));
        }
    }
}
