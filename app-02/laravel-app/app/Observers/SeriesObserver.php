<?php

namespace App\Observers;

use App\Models\Series;
use Illuminate\Support\Str;

class SeriesObserver
{
    public function creating(Series $series): void
    {
        $series->uuid ??= Str::uuid();
    }
}
