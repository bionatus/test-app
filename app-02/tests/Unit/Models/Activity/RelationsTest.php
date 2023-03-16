<?php

namespace Tests\Unit\Models\Activity;

use App\Models\Activity;
use App\Models\RelatedActivity;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Activity $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected Activity $activityInstance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityInstance = Activity::factory()->create();
    }

    /** @test */
    public function it_has_related_activity()
    {
        RelatedActivity::factory()->usingActivity($this->activityInstance)->count(self::COUNT)->create();

        $related = $this->activityInstance->relatedActivity()->get();

        $this->assertCorrectRelation($related, RelatedActivity::class);
    }
}
