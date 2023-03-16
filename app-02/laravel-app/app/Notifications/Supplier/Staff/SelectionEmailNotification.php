<?php

namespace App\Notifications\Supplier\Staff;

use App;
use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Mail\Supplier\SelectionEmail;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SelectionEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Supplier $supplier;
    protected Staff    $staff;
    protected bool     $shouldSendSupplierEmail;

    public function __construct(Staff $staff, Supplier $supplier, bool $shouldSendSupplierEmail)
    {
        $this->supplier                = $supplier;
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
        return (new SelectionEmail($this->supplier))->to($notifiable->email, $notifiable->name)
            ->bcc($notifiable->secondary_email, $notifiable->name);
    }
}
