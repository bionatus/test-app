<?php

namespace App\Exports;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOrderInvoiceCreationMonth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderInvoicesSuppliersExport implements WithMapping, WithHeadings, FromQuery
{
    use Exportable;

    public function query()
    {
        $month = Carbon::now()->subMonth()->month;

        return Supplier::scoped(new ByOrderInvoiceCreationMonth($month));
    }

    public function headings(): array
    {
        return [
            'id',
            'email',
            'name',
            'address',
            'city',
            'state',
            'zip_code',
            'country',
            'phone',
            'notes',
        ];
    }

    public function map($row): array
    {
        $supplier = $row;

        return [
            $supplier->id,
            $supplier->email,
            $supplier->name,
            $supplier->address,
            $supplier->city,
            $supplier->state,
            $supplier->zip_code,
            $supplier->country,
            $supplier->phone,
            (string) ($supplier->take_rate / 100),
        ];
    }
}
