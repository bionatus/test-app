<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface PerformsPartSearches
{
    public function partSearches(): HasMany;
}
