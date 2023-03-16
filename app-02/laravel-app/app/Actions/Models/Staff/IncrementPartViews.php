<?php

namespace App\Actions\Models\Staff;

use App\Models\Part;
use App\Models\Staff;
use App\Models\PartSearchCounter;
use Spatie\QueueableAction\QueueableAction;

class IncrementPartViews
{
    use QueueableAction;

    private Staff             $staff;
    private Part              $part;
    private PartSearchCounter $partSearchCounter;

    public function __construct(Staff $staff, Part $part, PartSearchCounter $partSearchCounter)
    {
        $this->staff             = $staff;
        $this->part              = $part;
        $this->partSearchCounter = $partSearchCounter;
    }

    public function execute()
    {
        $this->staff->partDetailCounters()->create([
            'part_id'                => $this->part->getKey(),
            'part_search_counter_id' => $this->partSearchCounter->getKey(),
        ]);
    }
}
