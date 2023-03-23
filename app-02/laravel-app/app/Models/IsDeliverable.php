<?php

namespace App\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;

interface IsDeliverable
{
    public static function usesDestinationAddress(): bool;

    public static function usesOriginAddress(): bool;

    public function hasDestinationAddress(): bool;

    public function hasOriginAddress(): bool;

    public function createSubstatusHandler(Order $order): OrderSubstatusUpdated;
}
