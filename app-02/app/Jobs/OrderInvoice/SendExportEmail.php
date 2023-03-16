<?php

namespace App\Jobs\OrderInvoice;

use App;
use App\Mail\OrderInvoice\ExportEmail;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendExportEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $type;
    private string $filePath;
    private string $emailSubject;

    public function __construct(string $type, string $filePath, string $emailSubject)
    {
        $this->type         = $type;
        $this->filePath     = $filePath;
        $this->emailSubject = $emailSubject;
    }

    public function handle()
    {
        $mailable = App::make(ExportEmail::class, [
            'filePath' => $this->filePath,
            'type'     => $this->type,
        ])->subject($this->emailSubject);
        Mail::to(Config::get('mail.reports.invoices'))->send($mailable);
    }
}
