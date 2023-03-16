<?php

namespace App\Actions\Models\User;

use App\Models\Part;
use App\Models\User;
use App\Models\PartSearchCounter;
use Spatie\QueueableAction\QueueableAction;

class IncrementPartViews
{
    use QueueableAction;

    private User              $user;
    private Part              $part;
    private PartSearchCounter $partSearchCounter;

    public function __construct(User $user, Part $part, PartSearchCounter $partSearchCounter)
    {
        $this->user              = $user;
        $this->part              = $part;
        $this->partSearchCounter = $partSearchCounter;
    }

    public function execute()
    {
        $this->user->partDetailCounters()->create([
            'part_id'                => $this->part->getKey(),
            'part_search_counter_id' => $this->partSearchCounter->getKey(),
        ]);
    }
}
