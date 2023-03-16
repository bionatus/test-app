<?php

namespace Tests\Unit\Http\Requests\Api\V2\Post\Comment;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Post\Comment\StoreRequest;
use Exception;
use Illuminate\Http\UploadedFile;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CommentController */
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
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function its_body_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function it_should_limit_the_body_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::MESSAGE,
                'max'       => 1000,
            ]),
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_should_pass_validation_on_valid_data()
    {
        $image   = UploadedFile::fake()->image('avatar.jpeg')->size(1024);
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE => 'A message',
            RequestKeys::IMAGES  => [$image],
        ]);

        $request->assertValidationPassed();
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
            Lang::get('validation.max.array', [
                'attribute' => RequestKeys::IMAGES,
                'max'       => 3,
            ]),
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
        $request->assertValidationMessages(["Each item in images may not be greater than {$size} kilobytes."]);
    }
}
