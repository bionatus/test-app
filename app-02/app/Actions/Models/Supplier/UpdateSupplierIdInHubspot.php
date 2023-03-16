<?php

namespace App\Actions\Models\Supplier;

use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;

class UpdateSupplierIdInHubspot
{
    public function execute()
    {
        $hubspot   = app(Hubspot::class);
        $suppliers = Supplier::whereNotNull('hubspot_id')->get();
        $suppliers->map(fn(Supplier $supplier) => $hubspot->updateCompanySupplierId($supplier->getKey(),
            $supplier->hubspot_id));
    }
}
