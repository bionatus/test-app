<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Order\OrderEvent;
use App\Models\Setting;
use App\Notifications\Supplier\OrderApprovedNotification as OrderApprovedNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderApprovedEmailNotification as OrderApprovedEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderApprovedSmsNotification as OrderApprovedSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEvent $event)
    {
        $order    = $event->order();
        $supplier = $order->supplier;

        $supplier->notify(new OrderApprovedNotificationToSupplier($order));

        $slugEmailSetting        = Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL;
        $slugSmsSetting          = Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS;
        $shouldSendSupplierEmail = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSupplierSms   = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();

        foreach ($supplier->counters as $counterStaff) {
            $counterStaff->notify(new OrderApprovedEmailNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierEmail));
            $counterStaff->notify(new OrderApprovedSmsNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierSms));
        }
    }
}
