<?php

namespace App\Actions\Models;

use App\Models\PartSearchCounter;
use App\Models\PerformsPartSearches;
use Spatie\QueueableAction\QueueableAction;

class IncrementPartSearches
{
    use QueueableAction;

    private PerformsPartSearches $actor;
    private string               $criteria;
    private int                  $results;

    public function __construct(PerformsPartSearches $actor, string $criteria, int $results)
    {
        $this->actor    = $actor;
        $this->criteria = $criteria;
        $this->results  = $results;
    }

    public function execute(): PartSearchCounter
    {
        /** @var PartSearchCounter */
        return $this->actor->partSearches()->create([
            'criteria' => $this->criteria,
            'results'  => $this->results,
        ]);
    }
}
