<?php

namespace App\Actions\Models\Supply;

use App;
use App\Models\Scopes\ByName;
use App\Models\Scopes\Visible;
use App\Models\Supply;
use Str;

class SearchCharacterProximity
{
    private string $newSearchString;
    private int    $repeat;
    private int    $maxSearch;
    private int    $minCharacter;

    public function __construct(
        string $newSearchString,
        int $repeat,
        int $maxSearch,
        int $minCharacter,
        array $orderScopes = []
    ) {
        $this->newSearchString = $newSearchString;
        $this->repeat          = $repeat;
        $this->maxSearch       = $maxSearch;
        $this->minCharacter    = $minCharacter;
        $this->orderScopes     = $orderScopes;
    }

    public function execute(): array
    {
        $newSearchString = $this->newSearchString;
        $repeat          = $this->repeat;
        $maxSearch       = $this->maxSearch;
        $minCharacter    = $this->minCharacter;
        $orderScopes     = $this->orderScopes;

        do {
            $searchString = $newSearchString;
            $repeat       += 1;
            $supplies     = Supply::with('supplyCategory')->scoped(new ByName($searchString))->scoped(new Visible());

            foreach ($orderScopes as $orderScope) {
                $supplies->scoped($orderScope);
            }
            $supplies = $supplies->paginate();

            $suppliesCount   = $supplies->count();
            $newSearchString = Str::substr($newSearchString, 0, -1);
            $canSearch       = Str::length($newSearchString) >= $minCharacter && $repeat <= $maxSearch && $suppliesCount == 0;
        } while ($canSearch);

        return [$supplies, $searchString];
    }
}
