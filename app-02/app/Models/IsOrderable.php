<?php

namespace App\Models;

interface IsOrderable
{
    public function getReadableTypeAttribute(): string;
}
