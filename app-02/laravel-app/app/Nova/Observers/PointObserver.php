<?php

namespace App\Nova\Observers;

use App;
use App\Models\Point;
use Auth;

class PointObserver
{
    public function saving(Point $point)
    {
        $authUser = Auth::user();

        $point->object_type = $authUser->getMorphClass();
        $point->object_id   = $authUser->getKey();
        $point->action      = Point::ACTION_ADJUSTMENT;
        $point->coefficient = 1;
        $point->multiplier  = 1;
    }

    public function saved(Point $point)
    {
        $point->user->processLevel();
    }
}
