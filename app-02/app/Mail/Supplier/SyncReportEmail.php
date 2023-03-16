<?php

namespace App\Mail\Supplier;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SyncReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private CarbonInterface $startedAt;
    private CarbonInterface $completedAt;
    private int             $processedRecords;
    private int             $malformedRecords;
    private Collection      $createdIds;
    private Collection      $updatedIds;
    private Collection      $deletedIds;
    private Collection      $failedGeocodeIds;
    private Collection      $errors;
    private Collection      $warnings;

    public function __construct(
        CarbonInterface $startedAt,
        CarbonInterface $completedAt,
        int $processedRecords,
        Collection $createdIds,
        Collection $updatedIds,
        Collection $deletedIds,
        Collection $failedGeocodeIds,
        Collection $errors,
        Collection $warnings,
        int $malformedRecords
    ) {
        $this->startedAt        = $startedAt;
        $this->completedAt      = $completedAt;
        $this->processedRecords = $processedRecords;
        $this->createdIds       = $createdIds;
        $this->updatedIds       = $updatedIds;
        $this->deletedIds       = $deletedIds;
        $this->failedGeocodeIds = $failedGeocodeIds;
        $this->errors           = $errors;
        $this->warnings         = $warnings;
        $this->malformedRecords = $malformedRecords;
    }

    public function build()
    {
        $data = [
            'startedAt'        => $this->startedAt,
            'completedAt'      => $this->completedAt,
            'processedRecords' => $this->processedRecords,
            'createdIds'       => $this->createdIds,
            'updatedIds'       => $this->updatedIds,
            'deletedIds'       => $this->deletedIds,
            'failedGeocodeIds' => $this->failedGeocodeIds,
            'errors'           => $this->errors,
            'warnings'         => $this->warnings,
            'malformedRecords' => $this->malformedRecords,
        ];

        return $this->view('emails.supplier.sync-report', $data);
    }
}
