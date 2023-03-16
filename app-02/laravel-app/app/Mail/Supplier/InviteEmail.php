<?php

namespace App\Mail\Supplier;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
        $this->onConnection('database');
    }

    public function build()
    {
        $data = [
            'supplierName' => $this->supplier->name,
        ];

        return $this->view('emails.supplier.invite', $data);
    }
}
