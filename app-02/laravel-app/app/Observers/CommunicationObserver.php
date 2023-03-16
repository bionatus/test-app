<?php

namespace App\Observers;

use App\Models\Communication;
use Illuminate\Support\Str;

class CommunicationObserver
{
    public function creating(Communication $communication): void
    {
        $communication->uuid = Str::uuid();
    }
}
