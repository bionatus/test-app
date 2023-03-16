<?php

namespace App\Mail\CommonItem;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SyncCommonItemReportEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private CarbonInterface $startedAt;
    private CarbonInterface $completedAt;
    private int             $processedRecords;
    private int             $malformedRecords;
    private Collection      $createdIds;
    private Collection      $updatedIds;
    private Collection      $errors;

    public function __construct(
        CarbonInterface $startedAt,
        CarbonInterface $completedAt,
        Collection $createdIds,
        Collection $updatedIds,
        Collection $errors,
        int $processedRecords,
        int $malformedRecords
    ) {
        $this->startedAt        = $startedAt;
        $this->completedAt      = $completedAt;
        $this->processedRecords = $processedRecords;
        $this->createdIds       = $createdIds;
        $this->updatedIds       = $updatedIds;
        $this->errors           = $errors;
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
            'errors'           => $this->errors,
            'malformedRecords' => $this->malformedRecords,
        ];

        return $this->view('emails.commonItem.sync-common-item-report', $data);
    }
}
