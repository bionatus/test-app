<?php

namespace App\Notifications\Supplier\Staff;

use App;
use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Actions\Models\Supplier\GetSupplierRoutesUrl;
use App\Mail\Supplier\NewMessageEmail;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PubnubNewMessageEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Supplier $supplier;
    protected User     $user;
    protected Staff    $staff;
    protected string   $message;
    protected bool     $shouldSendSupplierEmail;

    public function __construct(
        Supplier $supplier,
        User $user,
        string $message,
        Staff $staff,
        bool $shouldSendSupplierEmail
    ) {
        $this->supplier                = $supplier;
        $this->user                    = $user;
        $this->staff                   = $staff;
        $this->message                 = $message;
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
        $linkUrl = App::make(GetSupplierRoutesUrl::class, ['supplier' => $this->supplier, 'user' => $this->user])
            ->execute();

        return (new NewMessageEmail($this->supplier, $this->user, $this->message, $linkUrl))->to($notifiable->email,
            $notifiable->name)->bcc($notifiable->secondary_email, $notifiable->name);
    }
}
