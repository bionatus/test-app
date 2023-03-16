<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Requests\Api\V2\Post\StoreRequest;
use App\Models\PlainTag;
use App\Models\Series;
use App\Rules\ExistingIncomingRawTag;
use App\Types\TaggablesCollection;
use Exception;
use Illuminate\Http\UploadedFile;
use Lang;
use Mockery;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see PostController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_body()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
    }

    /** @test */
    public function its_body_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
    }

    /** @test */
    public function it_should_limit_the_body_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([
            RequestKeys::MESSAGE,
        ]);
    }

    /** @test */
    public function it_requires_tags()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['You must select at least one tag.']);
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
    public function it_should_limit_tags_to_five()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAGS => [1, 2, 3, 4, 5, 6]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['The tags may not have more than 5 items.']);
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
    public function it_returns_taggables_collection()
    {
        $taggablesCollection = new TaggablesCollection();
        $mock                = Mockery::mock(ExistingIncomingRawTag::class)->makePartial();
        $mock->shouldReceive('taggables')->withNoArgs()->andReturn($taggablesCollection);

        $request = new StoreRequest();
        $this->assertEquals($taggablesCollection, $request->taggables());
    }

    /** @test
     * @throws Exception
     */
    public function it_should_fail_validation_when_two_series_are_sent()
    {
        $this->refreshDatabaseForSingleTest();

        $seriesOne = Series::factory()->create();
        $seriesTwo = Series::factory()->create();

        $data = [
            RequestKeys::TAGS => [
                $seriesOne->toTagType()->toArray(),
                $seriesTwo->toTagType()->toArray(),
            ],
        ];

        $request = $this->formRequest($this->requestClass, $data);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['Only one series is allowed.']);
    }

    /** @test
     * @throws Exception
     */
    public function it_should_fail_validation_when_two_tags_of_type_more_are_sent()
    {
        $this->refreshDatabaseForSingleTest();

        $typeMoreOne = PlainTag::factory()->more()->create();
        $typeMoreTwo = PlainTag::factory()->more()->create();

        $data = [
            RequestKeys::TAGS => [
                $typeMoreOne->toTagType()->toArray(),
                $typeMoreTwo->toTagType()->toArray(),
            ],
        ];

        $request = $this->formRequest($this->requestClass, $data);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['Only one tag of type more is allowed.']);
    }

    /** @test */
    public function its_images_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => 'just a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => 'images'])]);
    }

    /** @test */
    public function it_should_limit_images_to_five()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => [1, 2, 3, 4, 5, 6]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.max.array', ['attribute' => 'images', 'max' => 5])]);
    }

    /** @test */
    public function it_should_only_allow_files()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => ['just a string']]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in images must be a file.']);
    }

    /** @test */
    public function each_item_in_its_images_parameter_must_be_an_image()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => [$file]]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in images must be of type: jpg, jpeg, png, gif, heic.']);
    }

    /** @test */
    public function it_should_fail_validation_on_files_larger_than_ten_megabytes()
    {
        $image   = UploadedFile::fake()->image('avatar.jpeg')->size(1024 * 11);
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => [$image]]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $size = 1024 * 10;
        $request->assertValidationMessages(["Each item in images may not be greater than $size kilobytes."]);
    }
}
