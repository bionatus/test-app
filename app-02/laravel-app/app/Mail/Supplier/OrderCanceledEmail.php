<?php

namespace App\Mail\Supplier;

use App\Models\Order;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCanceledEmail extends Mailable implements ShouldQueue
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
        $linkUrl          = $baseLiveUrl . Config::get('live.routes.outbound');
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');
        $order            = $this->order;
        $supplier         = $order->supplier;
        $user             = $order->user;

        $data = [
            'bidNumber'        => $order->bid_number,
            'supplierAddress'  => $supplier->address,
            'supplierName'     => $supplier->name,
            'userCompanyName'  => $user->companyName(),
            'userFullName'     => $user->fullName(),
            'linkUrl'          => $linkUrl,
            'notificationsUrl' => $notificationsUrl,
        ];

        return $this->subject('BluonLive: An Order Has Been Canceled')->view('emails.supplier.order.canceled', $data);
    }
}
