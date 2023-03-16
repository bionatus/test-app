<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface PerformsSupplySearches
{
    public function supplySearches(): HasMany;
}
