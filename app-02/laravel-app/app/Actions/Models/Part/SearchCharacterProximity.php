<?php

namespace App\Actions\Models\Part;

use App;
use App\Models\Part;
use App\Models\Part\Scopes\ByNumber;
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
        $orderScopes     = $this->orderScopes;

        do {
            $searchString = $newSearchString;
            $repeat       += 1;
            $parts        = Part::with('item')->scoped(new ByNumber($searchString));

            foreach ($orderScopes as $orderScope) {
                $parts->scoped($orderScope);
            }

            $parts = $parts->paginate();

            $partsCount      = $parts->count();
            $newSearchString = Str::substr($newSearchString, 0, -1);
            $canSearch       = Str::length($newSearchString) >= $minCharacter && $repeat <= $maxSearch && $partsCount == 0;
        } while ($canSearch);

        return [$parts, $searchString];
    }
}
