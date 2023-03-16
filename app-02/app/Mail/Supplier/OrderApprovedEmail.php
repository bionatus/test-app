<?php

namespace App\Mail\Supplier;

use App\Models\Order;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderApprovedEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $baseLiveUrl      = Config::get('live.url');
        $outboundUrl      = $baseLiveUrl . Config::get('live.routes.outbound');
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');

        $data = [
            'bid'              => $this->order->bid_number,
            'companyName'      => $this->order->user->companyName(),
            'supplierName'     => $this->order->supplier->name,
            'userName'         => $this->order->user->fullName(),
            'outboundUrl'      => $outboundUrl,
            'notificationsUrl' => $notificationsUrl,
        ];

        return $this->subject('BluonLive: An Order Has Been Approved')->view('emails.supplier.order.approved', $data);
    }
}
