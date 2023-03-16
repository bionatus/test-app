<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Scopes\ByName;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByNameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_plain_tags_by_name_without_a_strict_search()
    {
        $expected = PlainTag::factory()->create(['name' => 'Regular Plain Tag']);
        PlainTag::factory()->count(2)->create();

        $tags = PlainTag::scoped(new ByName('regular'))->get();

        $this->assertCount(1, $tags);
        $this->assertSame($expected->getKey(), $tags->first()->getKey());
    }

    /** @test */
    public function it_filters_series_by_name_without_a_strict_search()
    {
        $expected = Series::factory()->create(['name' => 'Regular Series']);
        Series::factory()->count(2)->create();

        $series = Series::scoped(new ByName('regular'))->get();

        $this->assertCount(1, $series);
        $this->assertSame($expected->getKey(), $series->first()->getKey());
    }

    /** @test */
    public function it_filters_system_by_name_without_a_strict_search()
    {
        $expected = ModelType::factory()->create(['name' => 'Regular System']);
        ModelType::factory()->count(2)->create();

        $modelTypes = ModelType::scoped(new ByName('regular'))->get();

        $this->assertCount(1, $modelTypes);
        $this->assertSame($expected->getKey(), $modelTypes->first()->getKey());
    }

    /** @test */
    public function it_filters_users_by_name_without_a_strict_search()
    {
        $expected = User::factory()->create([
            'first_name' => 'Regular',
            'last_name'  => 'User',
        ]);
        User::factory()->count(2)->create();

        $users = User::scoped(new ByName('regular'))->get();

        $this->assertCount(1, $users);
        $this->assertSame($expected->getKey(), $users->first()->getKey());
    }
}
