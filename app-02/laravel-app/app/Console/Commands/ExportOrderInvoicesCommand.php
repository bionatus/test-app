<?php

namespace App\Console\Commands;

use App;
use App\Constants\Filesystem;
use App\Exports\OrderInvoicesExport;
use App\Jobs\OrderInvoice\SendExportEmail;
use App\Jobs\OrderInvoice\ToProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;
use Storage;

class ExportOrderInvoicesCommand extends Command
{
    const DATE_FORMAT = 'Y-m';
    protected $signature   = 'export:invoices';
    protected $description = 'Export order invoices to CSV';

    public function handle()
    {
        $export       = App::make(OrderInvoicesExport::class);
        $type         = 'Invoices';
        $emailSubject = 'Invoices Export ' . Carbon::now()->subMonth()->format(self::DATE_FORMAT);
        $filename     = 'invoices-' . Carbon::now()->subMonth()->format(self::DATE_FORMAT) . '.csv';
        $filePath     = Storage::disk(Filesystem::DISK_EXPORTS)->path($filename);

        $sendExportEmail = App::make(SendExportEmail::class, [
            'type'         => $type,
            'filePath'     => $filePath,
            'emailSubject' => $emailSubject,
        ]);
        $toProcess       = App::make(ToProcess::class);

        $export->queue($filename, Filesystem::DISK_EXPORTS, Excel::CSV)->chain([
            $sendExportEmail,
            $toProcess,
        ]);
    }
}
