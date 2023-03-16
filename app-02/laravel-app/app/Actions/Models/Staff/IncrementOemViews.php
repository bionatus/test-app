<?php

namespace App\Actions\Models\Staff;

use App\Models\Oem;
use App\Models\Staff;
use App\Models\OemSearchCounter;
use Spatie\QueueableAction\QueueableAction;

class IncrementOemViews
{
    use QueueableAction;

    private Staff            $staff;
    private Oem              $oem;
    private OemSearchCounter $oemSearchCounter;

    public function __construct(Staff $staff, Oem $oem, OemSearchCounter $oemSearchCounter)
    {
        $this->staff            = $staff;
        $this->oem              = $oem;
        $this->oemSearchCounter = $oemSearchCounter;
    }

    public function execute()
    {
        $this->staff->oemDetailCounters()->create([
            'oem_id'                => $this->oem->getKey(),
            'oem_search_counter_id' => $this->oemSearchCounter->getKey(),
        ]);
    }
}
