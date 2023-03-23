<?php

namespace App\Console\Commands\Invoices;

use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedBetween;
use App\Models\OrderInvoice\Scopes\NotProcessed;
use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SetProcessedAtInOldInvoices extends Command
{
    protected $signature   = 'invoices:set-processed-at-for-old-invoices';
    protected $description = 'Set processed_at for old invoices';

    public function handle()
    {
        $exportDay         = Config::get('scheduler.export-order-invoices.day');
        $exportHour        = Config::get('scheduler.export-order-invoices.hour');
        $exportTimezone    = Config::get('scheduler.export-order-invoices.timezone');
        $now               = Carbon::now($exportTimezone);
        $currentExportDate = $now->clone()->startOfDay()->day($exportDay)->hour($exportHour);
        $minDate           = OrderInvoice::scoped(new NotProcessed())->min('created_at');
        $exportDate        = $now->lessThan($currentExportDate) ? $currentExportDate->subMonth() : $currentExportDate;

        while ($minDate && $exportDate->greaterThan($minDate)) {
            $fromDate = $exportDate->clone()->subMonth()->startOfMonth()->toDateTimeString();
            $tillDate = $exportDate->clone()->subMonth()->endOfMonth()->toDateTimeString();

            OrderInvoice::scoped(new ByCreatedBetween($fromDate, $tillDate))->scoped(new NotProcessed())->update([
                'processed_at' => $exportDate,
            ]);

            $exportDate->subMonth();
        }
    }
}
