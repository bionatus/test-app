<?php

namespace App\Nova\Observers;

use App\Events\Supplier\Selected;
use App\Events\Supplier\Unselected;
use App\Events\User\SuppliersUpdated;
use App\Models\SupplierUser;

class SupplierUserObserver
{
    public function created(SupplierUser $supplierUser)
    {
        SuppliersUpdated::dispatch($supplierUser->user);
        Selected::dispatch($supplierUser->supplier);
    }

    public function deleted(SupplierUser $supplierUser)
    {
        SuppliersUpdated::dispatch($supplierUser->user);
        Unselected::dispatch($supplierUser->supplier);
    }
}
