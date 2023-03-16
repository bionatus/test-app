<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Events\Order\Created as CreatedEvent;
use App\Models\Setting;
use App\Notifications\Supplier\OrderCreatedNotification as OrderCreatedNotificationToSupplier;
use App\Notifications\Supplier\Staff\OrderCreatedEmailNotification as OrderCreatedEmailNotificationToStaff;
use App\Notifications\Supplier\Staff\OrderCreatedSmsNotification as OrderCreatedSmsNotificationToStaff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedEvent $event)
    {
        $order    = $event->order();
        $supplier = $order->supplier;

        $supplier->notify(new OrderCreatedNotificationToSupplier($order));

        $slugEmailSetting        = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL;
        $slugSmsSetting          = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS;
        $shouldSendSupplierEmail = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSupplierSms   = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();

        foreach ($supplier->counters as $counterStaff) {
            $counterStaff->notify(new OrderCreatedEmailNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierEmail));
            $counterStaff->notify(new OrderCreatedSmsNotificationToStaff($order, $counterStaff,
                $shouldSendSupplierSms));
        }
    }
}
