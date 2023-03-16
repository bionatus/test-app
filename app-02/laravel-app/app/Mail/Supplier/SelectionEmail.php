<?php

namespace App\Mail\Supplier;

use App\Models\Supplier;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SelectionEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function build()
    {
        $baseLiveUrl      = Config::get('live.url');
        $accountUrl       = $baseLiveUrl . Config::get('live.account.customers');
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');

        $data = [
            'supplierName'     => $this->supplier->name,
            'accountUrl'       => $accountUrl,
            'notificationsUrl' => $notificationsUrl,
        ];

        return $this->subject('BluonLive: New Bluon Member to be Verified')->view('emails.supplier.selection', $data);
    }
}
