<?php

namespace Tests\Unit\Models\Part;

use App\Constants\RelationsMorphs;
use App\Models\Item;
use App\Models\Oem;
use App\Models\OemPart;
use App\Models\Part;
use App\Models\PartDetailCounter;
use App\Models\PartNote;
use App\Models\RecommendedReplacement;
use App\Models\Replacement;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Part $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Part::factory()->create();
    }

    /** @test */
    public function it_is_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_has_oems()
    {
        OemPart::factory()->usingPart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oems()->get();

        $this->assertCorrectRelation($related, Oem::class);
    }

    /** @test */
    public function it_has_oem_parts()
    {
        OemPart::factory()->usingPart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->oemParts()->get();

        $this->assertCorrectRelation($related, OemPart::class);
    }

    /** @test
     * @noinspection PhpUndefinedMethodInspection
     */
    public function it_can_be_morphed_to_a_part_type()
    {
        Collection::make(Part::TYPES)->each(function(string $type) {
            $typeClass = RelationsMorphs::MAP[$type];
            $typeObj   = $typeClass::factory()->create();
            $part      = $typeObj->part()->first();

            $this->assertInstanceOf($typeClass, $part->detail()->first());
        });
    }

    /** @test */
    public function it_has_replacements()
    {
        Replacement::factory()->usingPart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->replacements()->get();

        $this->assertCorrectRelation($related, Replacement::class);
    }

    /** @test */
    public function it_belongs_to_a_tip()
    {
        $tip     = Tip::factory()->create();
        $part    = Part::factory()->usingTip($tip)->create();
        $related = $part->tip()->first();

        $this->assertInstanceOf(Tip::class, $related);
    }

    /** @test */
    public function it_has_part_detail_counters()
    {
        PartDetailCounter::factory()->usingPart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->partDetailCounters()->get();

        $this->assertCorrectRelation($related, PartDetailCounter::class);
    }

    /** @test */
    public function it_has_staff()
    {
        PartDetailCounter::factory()->withStaff()->usingPart($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->staff()->get();

        $this->assertCorrectRelation($related, Staff::class);
    }

    /** @test */
    public function it_has_a_note()
    {
        PartNote::factory()->usingPart($this->instance)->create();

        $related = $this->instance->note()->first();

        $this->assertInstanceOf(PartNote::class, $related);
    }

    /** @test */
    public function it_has_users()
    {
        PartDetailCounter::factory()->withUser()->usingPart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->users()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_recommended_replacements()
    {
        $supplier = Supplier::factory()->createQuietly();
        RecommendedReplacement::factory()->usingPart($this->instance)->usingSupplier($supplier)->count(self::COUNT)->create();

        $related = $this->instance->recommendedReplacements()->get();

        $this->assertCorrectRelation($related, RecommendedReplacement::class);
    }
}
