<?php

namespace App\Actions\Models;

use App\Models\OemSearchCounter;
use App\Models\PerformsOemSearches;
use Spatie\QueueableAction\QueueableAction;

class IncrementOemSearches
{
    use QueueableAction;

    private PerformsOemSearches $actor;
    private string              $criteria;
    private int                 $results;

    public function __construct(PerformsOemSearches $actor, string $criteria, int $results)
    {
        $this->actor    = $actor;
        $this->criteria = $criteria;
        $this->results  = $results;
    }

    public function execute(): OemSearchCounter
    {
        /** @var OemSearchCounter */
        return $this->actor->oemSearches()->create([
            'criteria' => $this->criteria,
            'results'  => $this->results,
        ]);
    }
}
