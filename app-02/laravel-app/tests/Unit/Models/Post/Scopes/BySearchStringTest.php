<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\ModelType;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Post\Scopes\BySearchString;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySearchStringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_a_non_empty_string()
    {
        Post::factory()->count(2)->create(['message' => 'A special post']);
        Post::factory()->create(['message' => 'A regular post']);
        Post::factory()->create(['message' => 'Regular post']);
        Post::factory()->create(['message' => 'Post regular']);

        $posts = Post::scoped(new BySearchString('regular'))->get();

        $this->assertCount(3, $posts);
    }

    /** @test */
    public function it_filters_nothing_on_an_empty_string()
    {
        Post::factory()->count(3)->create();

        $posts = Post::scoped(new BySearchString(''))->get();

        $this->assertCount(3, $posts);
    }

    /** @test */
    public function it_filters_nothing_on_null()
    {
        Post::factory()->count(3)->create();

        $posts = Post::scoped(new BySearchString(null))->get();

        $this->assertCount(3, $posts);
    }

    /** @test */
    public function it_filters_by_tag_name()
    {
        $plainTag  = PlainTag::factory()->create(['name' => 'Regular Plain Tag']);
        $series    = Series::factory()->create(['name' => 'Regular Series']);
        $modelType = ModelType::factory()->create(['name' => 'Regular ModelType']);
        Tag::factory()->usingPlainTag($plainTag)->count(1)->create();
        Tag::factory()->usingSeries($series)->count(2)->create();
        Tag::factory()->usingModelType($modelType)->count(3)->create();
        Post::factory()->count(2)->create();

        $posts = Post::scoped(new BySearchString('regular'))->get();

        $this->assertCount(6, $posts);
    }

    /** @test */
    public function it_filters_by_all()
    {
        $plainTag  = PlainTag::factory()->create(['name' => 'Regular Plain Tag']);
        $series    = Series::factory()->create(['name' => 'Regular Series']);
        $modelType = ModelType::factory()->create(['name' => 'Regular ModelType']);
        $user      = User::factory()->create([
            'first_name'  => 'Regular',
            'last_name'   => 'User',
            'public_name' => 'RegularUserPublic',

        ]);
        Tag::factory()->usingPlainTag($plainTag)->count(1)->create();
        Tag::factory()->usingSeries($series)->count(2)->create();
        Tag::factory()->usingModelType($modelType)->count(3)->create();
        Post::factory()->usingUser($user)->count(2)->create();
        Post::factory()->create(['message' => 'A regular post']);
        Post::factory()->create(['message' => 'Regular post']);
        Post::factory()->count(4)->create();

        $posts = Post::scoped(new BySearchString('regular'))->get();

        $this->assertCount(10, $posts);
    }

    /** @test */
    public function it_filters_by_public_name()
    {
        $user = User::factory()->create([
            'name'        => '',
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'public_name' => 'publicNameTest',
        ]);
        Post::factory()->usingUser($user)->count(3)->create();
        Post::factory()->count(2)->create();

        $posts = Post::scoped(new BySearchString('public'))->get();

        $this->assertCount(3, $posts);
    }
}
