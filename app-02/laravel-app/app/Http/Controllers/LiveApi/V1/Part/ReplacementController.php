<?php

namespace App\Http\Controllers\LiveApi\V1\Part;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Part\Replacement\BaseResource;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\Replacement\Scopes\SingleTypeFirst;
use App\Models\ReplacementNote;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\OldestKey;
use App\Models\SingleReplacement;

class ReplacementController extends Controller
{
    public function index(Part $part)
    {
        $replacementTableName     = Replacement::tableName();
        $replacementNoteTableName = ReplacementNote::tableName();
        $singleReplacementName    = SingleReplacement::tableName();
        $partName                 = Part::tableName();

        $replacements = $part->replacements()
            ->select(["$replacementTableName.*"])
            ->leftJoin($replacementNoteTableName, "$replacementTableName.id", '=', "replacement_id")
            ->leftJoin($singleReplacementName, "$replacementTableName.id", '=', "$singleReplacementName.id")
            ->leftJoin($partName, "$singleReplacementName.replacement_part_id", '=', "$partName.id")
            ->with('singleReplacement.part.item')
            ->scoped(new SingleTypeFirst())
            ->scoped(new AlphabeticallyWithNullLast("$replacementNoteTableName.value"))
            ->scoped(new AlphabeticallyWithNullLast("$partName.brand"))
            ->scoped(new OldestKey())
            ->paginate();

        return BaseResource::collection($replacements);
    }
}
