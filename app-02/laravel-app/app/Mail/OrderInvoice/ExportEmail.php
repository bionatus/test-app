<?php

namespace App\Mail\OrderInvoice;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;

class ExportEmail extends Mailable
{
    const MIME_TYPE = 'text/csv';
    private string $filePath;
    private string $type;

    public function __construct(string $filePath, string $type)
    {
        $this->filePath = $filePath;
        $this->type     = $type;
    }

    public function build()
    {
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        return $this->view('emails.orderInvoices.export', [
            'type'     => $this->type,
            'fromDate' => $fromDate,
            'endDate'  => $tillDate,
        ])->attach($this->filePath, [
            'mime' => self::MIME_TYPE,
        ]);
    }
}
