<?php

namespace Tests\Unit\Database\Seeders;

use App\Constants\Environments;
use App\Models\GroupedReplacement;
use App\Models\Replacement;
use App\Models\Scopes\ByUuid;
use Database\Seeders\EnvironmentSeeder;
use Database\Seeders\GroupedReplacementsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class GroupedReplacementsSeederTest extends TestCase
{
    use RefreshDatabase;

    const REPLACEMENT_ROUTE_KEYS = [
        'f544fccc-4659-448e-9c35-6022596f4ef2',
        '439d2787-2197-4fd2-9d4a-d228f2ab24e9',
        '879b314f-c932-4c93-ac09-18e4994472f5',
        '4eb3bd1d-e816-4c91-859f-febe8397850b',
    ];

    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(GroupedReplacementsSeeder::class);

        $this->assertTrue($reflection->implementsInterface(EnvironmentSeeder::class));
    }

    /** @test */
    public function it_uses_specific_replacement_route_keys()
    {
        $this->assertEqualsCanonicalizing(self::REPLACEMENT_ROUTE_KEYS,
            GroupedReplacementsSeeder::REPLACEMENT_ROUTE_KEYS);
    }

    /** @test */
    public function it_stores_replacements()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        foreach (self::REPLACEMENT_ROUTE_KEYS as $replacementRouteKey) {
            $this->assertDatabaseHas(Replacement::tableName(), [
                Replacement::routeKeyName() => $replacementRouteKey,
            ]);
        }
    }

    /** @test */
    public function it_does_not_store_the_replacements_again()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        $replacementsCount        = Replacement::count();
        $groupedReplacementsCount = GroupedReplacement::count();

        $seeder->run();

        $this->assertDatabaseCount(Replacement::tableName(), $replacementsCount);
        $this->assertDatabaseCount(GroupedReplacement::tableName(), $groupedReplacementsCount);
    }

    /** @test */
    public function it_stores_a_grouped_type_replacement_with_only_a_grouped_replacement()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        $replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[0];
        $replacement         = Replacement::scoped(new ByUuid($replacementRouteKey))->first();

        $this->assertSame(Replacement::TYPE_GROUPED, $replacement->type);
        $this->assertCount(1, $replacement->groupedReplacements);
    }

    /** @test */
    public function it_stores_a_grouped_type_replacement_with_multiple_grouped_replacements()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        $replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[1];
        $replacement         = Replacement::scoped(new ByUuid($replacementRouteKey))->first();

        $this->assertSame(Replacement::TYPE_GROUPED, $replacement->type);
        $this->assertCount(5, $replacement->groupedReplacements);
    }

    /** @test */
    public function it_stores_a_part_with_multiple_grouped_type_replacements()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        $replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[2];
        $replacement1        = Replacement::scoped(new ByUuid($replacementRouteKey))->first();
        $part                = $replacement1->part;

        $this->assertCount(3, $part->replacements);

        foreach ($part->replacements as $replacement) {
            $this->assertSame(Replacement::TYPE_GROUPED, $replacement->type);
            $this->assertCount(4, $replacement->groupedReplacements);
        }
    }

    /** @test */
    public function it_stores_a_part_with_mixed_type_replacements()
    {
        $seeder = new GroupedReplacementsSeeder();
        $seeder->run();

        $replacementRouteKey = self::REPLACEMENT_ROUTE_KEYS[3];
        $replacement         = Replacement::scoped(new ByUuid($replacementRouteKey))->first();
        $part                = $replacement->part;
        $replacement1        = $part->replacements[0];
        $replacement2        = $part->replacements[1];

        $this->assertCount(2, $part->replacements);
        $this->assertSame(Replacement::TYPE_GROUPED, $replacement1->type);
        $this->assertCount(4, $replacement1->groupedReplacements);
        $this->assertSame(Replacement::TYPE_SINGLE, $replacement2->type);
    }

    /** @test */
    public function it_runs_in_specific_environments()
    {
        $seeder   = new GroupedReplacementsSeeder();
        $expected = [
            Environments::LOCAL,
            Environments::DEVELOPMENT,
            Environments::QA,
            Environments::QA2,
            Environments::DEMO,
            Environments::STAGING,
            Environments::UAT,
        ];

        $this->assertEquals($expected, $seeder->environments());
    }
}
