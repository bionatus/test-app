<?php

namespace App\Actions\Models\Oem;

use App;
use App\Models\Oem;
use App\Models\Oem\Scopes\ByModel;
use App\Models\Oem\Scopes\Live;
use Str;

class SearchCharacterProximity
{
    private string $newSearchString;
    private int    $repeat;
    private int    $maxSearch;
    private int    $minCharacter;
    private array  $orderScopes;

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

        do {
            $searchString = $newSearchString;
            $repeat       += 1;
            $oems         = Oem::with(['parts', 'series.brand'])
                ->scoped(new ByModel($searchString))
                ->scoped(new Live());

            foreach ($this->orderScopes as $orderScope) {
                $oems->scoped($orderScope);
            }
            $oems = $oems->paginate();

            $oemsCount = $oems->count();
            $newSearchString = Str::substr($newSearchString, 0, -1);
            $canSearch = Str::length($newSearchString) >= $minCharacter && $repeat <= $maxSearch && $oemsCount == 0;
        } while ($canSearch);

        return [$oems, $searchString];
    }
}
