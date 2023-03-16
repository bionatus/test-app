<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post\Comment;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\Post\CommentController;
use App\Http\Requests\Api\V2\Post\Comment\UpdateRequest;
use App\Models\Media;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CommentController */
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
    public function its_images_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => 'just a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::IMAGES])]);
    }

    /** @test */
    public function it_should_limit_images_to_three()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => [1, 2, 3, 4]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IMAGES]);
        $request->assertValidationMessages([
            Lang::get('validation.max.array', ['attribute' => RequestKeys::IMAGES, 'max' => 3]),
        ]);
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

    /** @test */
    public function it_should_have_current_images_parameter_if_it_has_images_parameter()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::IMAGES => [1, 2, 3, 4]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.present', ['attribute' => 'current images'])]);
    }

    /** @test */
    public function its_current_images_parameter_should_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CURRENT_IMAGES => 'just a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => 'current images'])]);
    }

    /** @test */
    public function it_should_limit_current_images_to_three()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::CURRENT_IMAGES => [1, 2, 3, 4]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES]);
        $request->assertValidationMessages([
            Lang::get('validation.max.array', ['attribute' => 'current images', 'max' => 3]),
        ]);
    }

    /** @test */
    public function each_item_in_its_current_images_parameter_must_be_an_uuid()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::CURRENT_IMAGES => ['just a string']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES . '.0']);
        $request->assertValidationMessages(['Each item in current images must be an uuid.']);
    }

    /** @test */
    public function each_item_in_the_current_images_parameter_should_exists()
    {
        $this->refreshDatabaseForSingleTest();

        $uuid    = (string) Str::uuid();
        $request = $this->formRequest($this->requestClass, [RequestKeys::CURRENT_IMAGES => [$uuid]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CURRENT_IMAGES . '.0']);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::CURRENT_IMAGES . '.0']),
        ]);
    }

    /** @test */
    public function it_should_limit_total_images_to_three()
    {
        $this->refreshDatabaseForSingleTest();
        $imageOne = UploadedFile::fake()->image('avatar.jpeg');
        $imageTwo = UploadedFile::fake()->image('avatar.jpeg');
        $uuidOne  = $this->faker->uuid;
        $uuidTwo  = $this->faker->uuid;

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IMAGES         => [$imageOne, $imageTwo],
            RequestKeys::CURRENT_IMAGES => $currentImages = [$uuidOne, $uuidTwo],
        ]);

        $maxImagesEnable = 3;
        $maxEnable = $maxImagesEnable - count($currentImages);

        $request->assertValidationFailed();
        $request->assertValidationMessages(['The images may not have more than ' . $maxEnable . ' items.']);
    }

    /** @test */
    public function it_should_ignore_duplicates_in_current_images()
    {
        $this->refreshDatabaseForSingleTest();
        $imageOne = UploadedFile::fake()->image('avatar.jpeg');
        $imageTwo = UploadedFile::fake()->image('avatar.jpeg');

        $media = Media::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE        => 'A message',
            RequestKeys::IMAGES         => [$imageOne, $imageTwo],
            RequestKeys::CURRENT_IMAGES => [$media->uuid, $media->uuid],
        ]);

        $request->assertValidationPassed();
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();
        $image = UploadedFile::fake()->image('avatar.jpeg')->size(1024);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE        => 'A message',
            RequestKeys::IMAGES         => [$image],
            RequestKeys::CURRENT_IMAGES => [],
        ]);

        $request->assertValidationPassed();
    }
}
