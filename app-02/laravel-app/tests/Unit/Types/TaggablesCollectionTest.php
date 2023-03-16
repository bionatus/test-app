<?php

namespace Tests\Unit\Types;

use App\Models\IsTaggable;
use App\Models\PlainTag;
use App\Models\Series;
use App\Models\Tag;
use App\Types\TaggablesCollection;
use App\Types\TaggableType;
use Exception;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class TaggablesCollectionTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_extends_eloquent_collection()
    {
        $taggables = new TaggablesCollection();

        $this->assertInstanceOf(Eloquent\Collection::class, $taggables);
    }

    /** @test */
    public function it_does_not_allow_invalid_items()
    {
        $this->expectException(Exception::class);

        new TaggablesCollection(['invalid']);
    }

    /** @test
     * @throws Exception
     */
    public function it_can_push_raw_tags()
    {
        $this->refreshDatabaseForSingleTest();
        $taggable  = Series::factory()->create();
        $taggables = new TaggablesCollection();

        $taggables->pushRawTag($taggable->toTagType()->toArray());

        $this->assertSame($taggable->getKey(), $taggables->first()->getKey());
    }

    /** @test
     * @throws Exception
     */
    public function it_supports_the_each_method()
    {
        $taggable = $this->getTaggableStub();

        $taggables = new TaggablesCollection([$taggable]);
        $check     = 0;

        $taggables->each(function() use (&$check) {
            $check = 1;
        });

        $this->assertSame(1, $check);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_an_array_containing_all_items()
    {
        $taggable = $this->getTaggableStub();

        $taggables = new TaggablesCollection([$taggable]);

        $all = $taggables->all();

        $this->assertIsArray($all);
        $this->assertSame(1, count($all));
        $this->assertEquals($taggable, $all[0]);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_collection_given_a_raw_array()
    {
        $this->refreshDatabaseForSingleTest();
        $series  = Series::factory()->create();
        $general = PlainTag::factory()->general()->create();
        $issue   = PlainTag::factory()->issue()->create();
        $more    = PlainTag::factory()->more()->create();

        $rawTags = [
            $series->toTagType()->toArray(),
            $general->toTagType()->toArray(),
            $issue->toTagType()->toArray(),
            $more->toTagType()->toArray(),
        ];

        $taggables = TaggablesCollection::fromRaw($rawTags);

        $this->assertCount(4, $taggables->all());

        $taggablesCollection = Collection::make($taggables->all());

        $returnedSeries = $taggablesCollection->first(function(IsTaggable $taggable) {
            return is_a($taggable, Series::class);
        });
        $this->assertEquals($returnedSeries->id, $series->id);
    }

    /** @test
     * @throws Exception
     */
    public function it_knows_if_it_is_empty()
    {
        $taggables = new TaggablesCollection([]);

        $this->assertTrue($taggables->isEmpty());
    }

    /** @test
     * @throws Exception
     */
    public function it_knows_if_it_has_elements()
    {
        $taggable = $this->getTaggableStub();

        $taggables = new TaggablesCollection([$taggable]);

        $this->assertTrue($taggables->isNotEmpty());
    }

    /** @test
     * @throws Exception
     */
    public function it_supports_the_map_method()
    {
        $taggable = $this->getTaggableStub();

        $taggables = new TaggablesCollection([$taggable, $taggable]);

        $mappedCollection = $taggables->map(function(IsTaggable $taggable) {
            return $taggable->getKey() . '-mapped';
        });

        $this->assertInstanceOf(Collection::class, $mappedCollection);
        $this->assertCount(2, $mappedCollection);
        $this->assertSame($taggable->getKey() . '-mapped', $mappedCollection->first());
    }

    private function getTaggableStub(): IsTaggable
    {
        return new class() implements IsTaggable {
            use InteractsWithMedia;

            public function morphType(): string
            {
                return '';
            }

            public function getKey()
            {
                return 'id';
            }

            public function getMorphClass()
            {
                return '';
            }

            public function toTagType(bool $withMedia = false): TaggableType
            {
                return new TaggableType([]);
            }

            public function taggableRouteKey(): string
            {
                return '';
            }

            public function tags(): MorphMany
            {
                return new MorphMany(new Eloquent\Builder(), new Tag(), '', '', '');
            }

            public function posts(): MorphToMany
            {
                return new MorphToMany(new Eloquent\Builder(), new Tag(), '', '', '', '', '', '');
            }
        };
    }
}
