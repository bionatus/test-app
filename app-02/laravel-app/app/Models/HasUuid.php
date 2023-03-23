<?php

namespace App\Models;

trait HasUuid
{
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
