<?php

namespace Tests\Unit\Models\UserTaggable\Scopes;

use App\Models\PlainTag;
use App\Models\UserTaggable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTaggableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_a_taggable()
    {
        $plainTag = PlainTag::factory()->create();

        UserTaggable::factory()->count(3)->create();
        UserTaggable::factory()->usingPlainTag($plainTag)->count(2)->create();

        $userTaggables = UserTaggable::scoped(new UserTaggable\Scopes\ByTaggable($plainTag))->get();

        $this->assertCount(2, $userTaggables);
    }
}
