<?php

namespace App\Mail\Supplier;

use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessageEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Supplier $supplier;
    private User     $user;
    private string   $message;
    private string   $linkUrl;

    public function __construct(Supplier $supplier, User $user, string $message, string $linkUrl)
    {
        $this->supplier = $supplier;
        $this->user     = $user;
        $this->message  = $message;
        $this->linkUrl  = $linkUrl;
    }

    public function build()
    {
        $baseLiveUrl      = Config::get('live.url');
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');

        $data = [
            'supplierName'     => $this->supplier->name,
            'userName'         => $this->user->fullName(),
            'messageText'      => $this->message,
            'datetime'         => Carbon::now()->tz($this->supplier->timezone)->format('M jS, h:i A'),
            'linkUrl'          => $this->linkUrl,
            'notificationsUrl' => $notificationsUrl,
        ];

        return $this->subject('BluonLive: You Have a New Message')->view('emails.supplier.new-message', $data);
    }
}
