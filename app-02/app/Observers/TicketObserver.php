<?php

namespace App\Observers;

use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketObserver
{
    public function creating(Ticket $ticket): void
    {
        $ticket->uuid = Str::uuid();
    }
}
