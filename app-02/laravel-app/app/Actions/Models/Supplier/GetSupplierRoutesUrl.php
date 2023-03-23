<?php

namespace App\Actions\Models\Supplier;

use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\BySubstatuses;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\ByUser;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Config;

class GetSupplierRoutesUrl
{
    private Supplier $supplier;
    private User     $user;

    public function __construct(Supplier $supplier, User $user)
    {
        $this->supplier = $supplier;
        $this->user     = $user;
    }

    public function execute()
    {
        $baseLiveUrl = Config::get('live.url');
        $inboundUrl  = $baseLiveUrl . Config::get('live.routes.inbound');
        $outboundUrl = $baseLiveUrl . Config::get('live.routes.outbound');

        $supplierHasUserUnprocessedOrders = Order::scoped(new ByUser($this->user))
            ->scoped(new BySupplier($this->supplier))
            ->scoped(new ByLastSubstatuses((array_merge(Substatus::STATUSES_PENDING,
                Substatus::STATUSES_PENDING_APPROVAL))))
            ->exists();
        if ($supplierHasUserUnprocessedOrders) {
            return $inboundUrl;
        }

        $supplierHasUserOrders = Order::scoped(new ByUser($this->user))
            ->scoped(new BySupplier($this->supplier))
            ->exists();
        if ($supplierHasUserOrders) {
            return $outboundUrl;
        }

        return $inboundUrl;
    }
}
