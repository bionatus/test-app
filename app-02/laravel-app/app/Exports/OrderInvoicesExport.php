<?php

namespace App\Exports;

use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedBetween;
use App\Models\OrderInvoice\Scopes\NotProcessed;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderInvoicesExport implements WithMapping, WithHeadings, FromQuery
{
    use Exportable;

    const DATE_FORMAT    = 'M-d-Y';
    const INVOICE_PREFIX = 'BL-';
    const ITEM           = 'BluonLive';

    public function query()
    {
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

        return OrderInvoice::with(['order.supplier', 'order.user.companyUser.company'])
            ->scoped(new ByCreatedBetween($fromDate, $tillDate))
            ->scoped(new NotProcessed());
    }

    public function headings(): array
    {
        return [
            'number',
            'customer_id',
            'customer',
            'date',
            'due_date',
            'payment_terms',
            'item',
            'quantity',
            'unit_cost',
            'line_item_metadata.date',
            'line_item_metadata.bid_number',
            'line_item_metadata.contractor_name',
            'line_item_metadata.po_number',
            'line_item_metadata.order_total',
            'line_item_metadata.transaction_fee',
            'branch',
        ];
    }

    public function map($row): array
    {
        $orderInvoice  = $row;
        $subtotal      = $orderInvoice->subtotal;
        $subtotal      = (string) ($orderInvoice->type === OrderInvoice::TYPE_CREDIT ? "($subtotal)" : $subtotal);
        $order         = $orderInvoice->order;
        $supplier      = $order->supplier;
        $user          = $order->user;
        $middleOfMonth = $orderInvoice->created_at->setDay(15)->format(self::DATE_FORMAT);

        return [
            self::INVOICE_PREFIX . $orderInvoice->number,
            $supplier->id,
            $supplier->name,
            $middleOfMonth,
            $middleOfMonth,
            $orderInvoice->payment_terms,
            self::ITEM,
            $subtotal,
            (string) ($orderInvoice->take_rate / 10000),
            $orderInvoice->created_at->format(self::DATE_FORMAT),
            (string) $orderInvoice->bid_number,
            $user ? $user->companyName() : $order->orderLockedData->user_company,
            $orderInvoice->order_name,
            $subtotal,
            $orderInvoice->take_rate / 100 . '%',
            $supplier->branch,
        ];
    }
}
