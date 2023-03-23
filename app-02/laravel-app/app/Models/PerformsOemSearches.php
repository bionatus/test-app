<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface PerformsOemSearches
{
    public function oemSearches(): HasMany;
}
