<?php

namespace App\Notifications\Supplier\Staff;

use App;
use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Mail\Supplier\OrderApprovedEmail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderApprovedEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected Staff $staff;
    protected bool  $shouldSendSupplierEmail;

    public function __construct(Order $order, Staff $staff, bool $shouldSendSupplierEmail)
    {
        $this->order                   = $order;
        $this->staff                   = $staff;
        $this->shouldSendSupplierEmail = $shouldSendSupplierEmail;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via   = [];
        $staff = $this->staff;

        $slugStaffEmailSetting = Setting::SLUG_STAFF_EMAIL_NOTIFICATION;
        $shouldSendStaffEmail  = App::make(GetNotificationSetting::class,
            ['staff' => $staff, 'slug' => $slugStaffEmailSetting])->execute();
        
        if ($staff->email && $this->shouldSendSupplierEmail && $shouldSendStaffEmail) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new OrderApprovedEmail($this->order))->to($notifiable->email, $notifiable->name)
            ->bcc($notifiable->secondary_email, $notifiable->name);
    }
}
