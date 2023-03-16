<?php

namespace App\Observers;

use App\Models\Staff;
use Illuminate\Support\Str;

class StaffObserver
{
    public function creating(Staff $staff): void
    {
        $staff->uuid = Str::uuid();
    }
}
