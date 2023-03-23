<?php

namespace App\Actions\Models;

use App\Models\SupplySearchCounter;
use App\Models\PerformsSupplySearches;
use Spatie\QueueableAction\QueueableAction;

class IncrementSupplySearches
{
    use QueueableAction;

    private PerformsSupplySearches $actor;
    private string                 $criteria;
    private int                    $results;

    public function __construct(PerformsSupplySearches $actor, string $criteria, int $results)
    {
        $this->actor    = $actor;
        $this->criteria = $criteria;
        $this->results  = $results;
    }

    public function execute(): SupplySearchCounter
    {
        /** @var SupplySearchCounter */
        return $this->actor->supplySearches()->create([
            'criteria' => $this->criteria,
            'results'  => $this->results,
        ]);
    }
}
