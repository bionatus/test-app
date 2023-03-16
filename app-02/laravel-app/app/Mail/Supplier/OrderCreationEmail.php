<?php

namespace App\Mail\Supplier;

use App\Models\Order;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreationEmail extends Mailable implements ShouldQueue
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
        $inboundUrl       = $baseLiveUrl . Config::get('live.routes.inbound');
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');

        $data = [
            'supplierName'     => $this->order->supplier->name,
            'supplierAddress'  => $this->order->supplier->address,
            'userName'         => $this->order->user->fullName(),
            'userFirstName'    => $this->order->user->first_name,
            'inboundUrl'       => $inboundUrl,
            'notificationsUrl' => $notificationsUrl,
        ];

        return $this->subject('BluonLive: New Order Request')->view('emails.supplier.order.creation', $data);
    }
}
