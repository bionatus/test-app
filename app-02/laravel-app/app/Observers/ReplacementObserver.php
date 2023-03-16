<?php

namespace App\Observers;

use App\Models\Replacement;
use Str;

class ReplacementObserver
{
    public function creating(Replacement $replacement):void
    {
        $replacement->uuid ??= Str::uuid();
    }
}
