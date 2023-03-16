<?php

namespace App\Console\Commands;

use App;
use App\Constants\Filesystem;
use App\Exports\OrderInvoicesSuppliersExport;
use App\Jobs\OrderInvoice\SendExportEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;
use Storage;

class ExportOrderInvoicesSuppliersCommand extends Command
{
    const DATE_FORMAT = 'Y-m';
    protected $signature   = 'export:invoices-customers';
    protected $description = 'Export order invoices customers to CSV';

    public function handle()
    {
        $export = App::make(OrderInvoicesSuppliersExport::class);

        $type         = 'Customers';
        $emailSubject = 'Customers Export ' . Carbon::now()->subMonth()->format(self::DATE_FORMAT);
        $filename     = 'customers-' . Carbon::now()->subMonth()->format(self::DATE_FORMAT) . '.csv';
        $filePath     = Storage::disk(Filesystem::DISK_EXPORTS)->path($filename);

        $sendExportEmail = App::make(SendExportEmail::class,
            ['type' => $type, 'filePath' => $filePath, 'emailSubject' => $emailSubject]);

        $export->queue($filename, Filesystem::DISK_EXPORTS, Excel::CSV)->chain([
            $sendExportEmail,
        ]);
    }
}
