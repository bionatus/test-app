<?php

namespace App\Observers;

use App\Models\SupportCall;
use Illuminate\Support\Str;

class SupportCallObserver
{
    public function creating(SupportCall $supportCall): void
    {
        $supportCall->uuid ??= Str::uuid();
    }
}
