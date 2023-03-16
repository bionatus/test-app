<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\UpdateRequest;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Series;
use App\Rules\ExistingIncomingRawTag;
use App\Types\TaggablesCollection;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Lang;
use Mockery;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;
    use WithFaker;

    protected string $requestClass = UpdateRequest::class;

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
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE => ['array item'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
    }

    /** @test */
    public function it_should_limit_the_body_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE => Str::random(1001),
        ]);

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
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => 'just a string',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['The tags must be an array.']);
    }

    /** @test */
    public function it_should_limit_tags_to_five()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [1, 2, 3, 4, 5, 6],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS]);
        $request->assertValidationMessages(['The tags may not have more than 5 items.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => ['just a string'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Each tag in tags must be an array.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_a_type()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [['just a string']],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag must have a "type" key.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_an_id()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [['type' => 'invalid']],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag must have an "id" key.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_a_string_type()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [['type' => [1], 'id' => [2]]],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag Type must be a string.']);
    }

    /** @test */
    public function each_item_in_its_tags_parameter_must_have_an_alpha_numeric_id()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [['type' => 'string', 'id' => [2]]],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Tag ID must be a string or integer.']);
    }

    /** @test */
    public function each_type_from_a_tag_in_its_tags_parameter_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TAGS => [['type' => 'invalid', 'id' => 2]],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAGS . '.0']);
        $request->assertValidationMessages(['Invalid tag.']);
    }

    /** @test
     * @throws Exception
     */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $series  = Series::factory()->create();
        $comment = Comment::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE  => Str::random(100),
            RequestKeys::TAGS     => [
                $series->toTagType()->toArray(),
            ],
            RequestKeys::SOLUTION => $comment->getRouteKey(),
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

        $request = new UpdateRequest();

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

    /** @test */
    public function its_images_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => 'just a string',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::IMAGES])]);
    }

    /** @test */
    public function it_should_limit_images_to_five()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => [1, 2, 3, 4, 5, 6],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);

        $request->assertValidationMessages([
            Lang::get('validation.max.array', [
                'attribute' => RequestKeys::IMAGES,
                'max'       => 5,
            ]),
        ]);
    }

    /** @test */
    public function it_should_only_allow_files()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => ['just a string'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in images must be a file.']);
    }

    /** @test */
    public function each_item_in_its_images_parameter_must_be_an_image()
    {
        $file    = UploadedFile::fake()->create('test.txt');
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => [$file],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in images must be of type: jpg, jpeg, png, gif, heic.']);
    }

    /** @test */
    public function it_should_fail_validation_on_files_larger_than_ten_megabytes()
    {
        $image   = UploadedFile::fake()->image('avatar.jpeg')->size(1024 * 11);
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => [$image],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES . '.0']);
        $size = 1024 * 10;
        $request->assertValidationMessages(["Each item in images may not be greater than {$size} kilobytes."]);
    }

    /** @test */
    public function it_should_have_current_images_parameter_if_it_has_images_parameter()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES => [1, 2, 3, 4, 5, 6],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.present', ['attribute' => 'current images'])]);
    }

    /** @test */
    public function its_current_images_parameter_should_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CURRENT_IMAGES => 'just a string',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => 'current images'])]);
    }

    /** @test */
    public function it_should_limit_current_images_to_five()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CURRENT_IMAGES => [1, 2, 3, 4, 5, 6],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([
            Lang::get('validation.max.array', ['attribute' => 'current images', 'max' => 5]),
        ]);
    }

    /** @test */
    public function each_item_in_its_current_images_parameter_must_be_an_uuid()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CURRENT_IMAGES => ['just a string'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in current images must be an uuid.']);
    }

    /** @test */
    public function each_item_in_the_current_images_parameter_should_exists()
    {
        $this->refreshDatabaseForSingleTest();

        $uuid    = (string) Str::uuid();
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CURRENT_IMAGES => [$uuid],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES . '.0']);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::CURRENT_IMAGES . '.0']),
        ]);
    }

    /** @test */
    public function it_should_limit_total_images_to_five()
    {
        $this->refreshDatabaseForSingleTest();

        $imageOne   = UploadedFile::fake()->image('avatar.jpeg');
        $imageTwo   = UploadedFile::fake()->image('avatar.jpeg');
        $imageThree = UploadedFile::fake()->image('avatar.jpeg');
        $uuidOne    = $this->faker->uuid;
        $uuidTwo    = $this->faker->uuid;
        $uuidThree  = $this->faker->uuid;

        $request   = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES         => [$imageOne, $imageTwo, $imageThree],
            RequestKeys::CURRENT_IMAGES => $currentImages = [$uuidOne, $uuidTwo, $uuidThree],
        ]);

        $maxImagesEnable = 5;
        $maxEnable = $maxImagesEnable - count($currentImages);
        $request->assertValidationFailed();
        $request->assertValidationMessages(['The images may not have more than ' . $maxEnable . ' items.']);
    }

    /** @test
     * @throws Exception
     */
    public function it_should_ignore_duplicates_in_current_images()
    {
        $this->refreshDatabaseForSingleTest();

        $imageOne = UploadedFile::fake()->image('avatar.jpeg');
        $imageTwo = UploadedFile::fake()->image('avatar.jpeg');

        $media = Media::factory()->create();

        $series = Series::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE        => 'A message',
            RequestKeys::IMAGES         => [$imageOne, $imageTwo],
            RequestKeys::CURRENT_IMAGES => [$media->uuid, $media->uuid],
            RequestKeys::TAGS           => [$series->toTagType()->toArray()],
        ]);

        $request->assertValidationPassed();
    }
}
