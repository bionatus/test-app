<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\IndexRequest;
use App\Models\Series;
use App\Models\Tag;
use App\Rules\ExistingIncomingRawTag;
use App\Types\TaggableType;
use DateTimeInterface;
use Exception;
use Illuminate\Support\Collection;
use Lang;
use Mockery;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_may_not_get_a_param()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_search_string_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
    }

    /** @test */
    public function it_should_limit_the_search_string_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([
            RequestKeys::SEARCH_STRING,
        ]);
    }

    /** @test */
    public function its_tags_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => 'just a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['The tags must be an array.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => ['just a string']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Each tag in tags must be an array.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_a_type()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [['just a string']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag must have a "type" key.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_an_id()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [['type' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag must have an "id" key.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_a_string_type()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [['type' => [1], 'id' => [2]]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag Type must be a string.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_an_alpha_numeric_id()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [['type' => 'string', 'id' => [2]]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag ID must be a string or integer.']);
    }

    /** @test */
    public function each_type_from_a_tag_in_its_tags_parameter_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [['type' => 'invalid', 'id' => 2]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Invalid tag.']);
    }

    /** @test */
    public function its_created_before_parameter_must_have_a_valid_atom_date_format()
    {
        $requestKey = RequestKeys::CREATED_BEFORE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => '2022/02/08']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'format'    => DateTimeInterface::ATOM,
            ]),
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $series = Series::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE => 'A message',
            RequestKeys::TAGS    => [
                $series->toTagType()->toArray(),
            ],
        ]);

        $request->assertValidationPassed();
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_collection_of_taggable_types()
    {
        $taggableType = new TaggableType([
            'id'   => 'an-id',
            'type' => Tag::TYPE_ISSUE,
        ]);
        $mock         = Mockery::mock(ExistingIncomingRawTag::class)->makePartial();
        $mock->shouldReceive('taggableTypes')->withNoArgs()->andReturn(Collection::make([$taggableType]));
        App::bind(ExistingIncomingRawTag::class, fn() => $mock);

        $indexRequest = new IndexRequest();
        $this->assertCollectionOfClass(TaggableType::class, $indexRequest->taggableTypes());
        $this->assertEquals($taggableType, $indexRequest->taggableTypes()->first());
    }

    /** @test */
    public function its_type_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::TYPE])]);
    }

    /** @test */
    public function its_type_must_be_a_valid_type()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 'foo']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::TYPE])]);
    }
}
