<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\User;
use App\Models\User\Scopes\ByFollowedTags;
use App\Models\UserTaggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByFollowedTagsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_followed_tags()
    {
        User::factory()->count(10)->create();
        $userTaggable1 = UserTaggable::factory()->series()->create();
        $userTaggable2 = UserTaggable::factory()->modelType()->create();
        $userTaggable3 = UserTaggable::factory()->general()->create();
        $userTaggable4 = UserTaggable::factory()->issue()->create();

        $followedTags     = new Collection([
            $userTaggable1,
            $userTaggable2,
            $userTaggable3,
            $userTaggable4,
        ]);
        $expectedUsersIds = [
            $userTaggable1->user->getKey(),
            $userTaggable2->user->getKey(),
            $userTaggable3->user->getKey(),
            $userTaggable4->user->getKey(),
        ];

        $filteredUsers    = User::scoped(new ByFollowedTags($followedTags))->get();
        $filteredUsersIds = $filteredUsers->pluck(User::keyName())->toArray();

        $this->assertEqualsCanonicalizing($expectedUsersIds, $filteredUsersIds);
    }

    /** @test */
    public function it_returns_the_user_only_once_if_the_user_follows_multiple_tags()
    {
        User::factory()->count(10)->create();
        $user               = User::factory()->create();
        $systemUserTaggable = UserTaggable::factory()->modelType()->usingUser($user)->create();
        $seriesUserTaggable = UserTaggable::factory()->series()->usingUser($user)->create();

        $followedTags     = new Collection([$systemUserTaggable, $seriesUserTaggable]);
        $expectedUsersIds = [
            $user->getKey(),
        ];

        $filteredUsers    = User::scoped(new ByFollowedTags($followedTags))->get();
        $filteredUsersIds = $filteredUsers->pluck(User::keyName())->toArray();

        $this->assertCount(1, $filteredUsersIds);
        $this->assertEqualsCanonicalizing($expectedUsersIds, $filteredUsersIds);
    }
}
