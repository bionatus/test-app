<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait IsPart
{
    public function part(): HasOne
    {
        return $this->hasOne(Part::class, 'id');
    }
}
