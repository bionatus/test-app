<?php

namespace App\Actions\Models\User;

use App\Models\Oem;
use App\Models\OemSearchCounter;
use App\Models\User;
use Spatie\QueueableAction\QueueableAction;

class IncrementOemViews
{
    use QueueableAction;

    private User             $user;
    private Oem              $oem;
    private OemSearchCounter $oemSearchCounter;

    public function __construct(User $user, Oem $oem, OemSearchCounter $oemSearchCounter)
    {
        $this->user             = $user;
        $this->oem              = $oem;
        $this->oemSearchCounter = $oemSearchCounter;
    }

    public function execute()
    {
        $this->user->oemDetailCounters()->create([
            'oem_id'                => $this->oem->getKey(),
            'oem_search_counter_id' => $this->oemSearchCounter->getKey(),
        ]);
    }
}
