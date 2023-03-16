<?php

namespace App\Mail\Supplier;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;

class ChangeRequestEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Supplier $supplier;
    private User     $user;
    private string   $reason;
    private ?string  $detail;

    public function __construct(Supplier $supplier, User $user, string $reason, string $detail = null)
    {
        $this->supplier = $supplier;
        $this->user     = $user;
        $this->reason   = $reason;
        $this->detail   = $detail;
        $this->onConnection('database');
    }

    public function build()
    {
        $supplier = $this->supplier;
        $country  = Country::build($this->supplier->country);
        $state    = State::build($this->supplier->state);

        $data = [
            'supplier' => $supplier,
            'user'     => $this->user,
            'reason'   => $this->reason,
            'detail'   => $this->detail,
            'country'  => $country,
            'state'    => $state,
        ];

        return $this->view('emails.supplier.change', $data);
    }
}
