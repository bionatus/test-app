<?php

namespace App\Mail\Supplier;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SyncStaffReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Collection $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function build()
    {
        $data = ['records' => $this->records];

        return $this->view('emails.supplier.sync-staff-report', $data);
    }
}
