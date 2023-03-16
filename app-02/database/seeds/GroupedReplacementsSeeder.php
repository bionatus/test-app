<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Models\GroupedReplacement;
use App\Models\Replacement;
use App\Models\Scopes\ByUuid;
use App\Models\SingleReplacement;
use Illuminate\Database\Seeder;

class GroupedReplacementsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    const REPLACEMENT_ROUTE_KEYS = [
        'f544fccc-4659-448e-9c35-6022596f4ef2',
        '439d2787-2197-4fd2-9d4a-d228f2ab24e9',
        '879b314f-c932-4c93-ac09-18e4994472f5',
        '4eb3bd1d-e816-4c91-859f-febe8397850b',
    ];

    public function run()
    {
        // Grouped type replacement with only a grouped replacement
        if (!$this->checkIfReplacementExists($replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[0])) {
            $replacement = Replacement::factory()
                ->grouped()
                ->create([Replacement::routeKeyName() => $replacementRouteKey]);
            GroupedReplacement::factory()->usingReplacement($replacement)->create();
        }

        // Grouped type replacement with multiple grouped replacements
        if (!$this->checkIfReplacementExists($replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[1])) {
            $replacement = Replacement::factory()
                ->grouped()
                ->create([Replacement::routeKeyName() => $replacementRouteKey]);
            GroupedReplacement::factory()->usingReplacement($replacement)->count(5)->create();
        }

        // Part with multiple grouped type replacements
        if (!$this->checkIfReplacementExists($replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[2])) {
            $replacement1 = Replacement::factory()
                ->grouped()
                ->create([Replacement::routeKeyName() => $replacementRouteKey]);
            $replacement2 = Replacement::factory()->grouped()->usingPart($replacement1->part)->create();
            $replacement3 = Replacement::factory()->grouped()->usingPart($replacement1->part)->create();
            GroupedReplacement::factory()->usingReplacement($replacement1)->count(4)->create();
            GroupedReplacement::factory()->usingReplacement($replacement2)->count(4)->create();
            GroupedReplacement::factory()->usingReplacement($replacement3)->count(4)->create();
        }

        // Part with mixed type replacements
        if (!$this->checkIfReplacementExists($replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[3])) {
            $replacement1 = Replacement::factory()
                ->grouped()
                ->create([Replacement::routeKeyName() => $replacementRouteKey]);
            $replacement2 = Replacement::factory()->single()->create([
                'original_part_id' => $replacement1->original_part_id,
            ]);
            GroupedReplacement::factory()->usingReplacement($replacement1)->count(4)->create();
            SingleReplacement::factory()->usingReplacement($replacement2)->create();
        }
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_PRODUCTION_OR_TESTING;
    }

    private function checkIfReplacementExists(string $uuid): bool
    {
        return Replacement::scoped(new ByUuid($uuid))->exists();
    }
}
