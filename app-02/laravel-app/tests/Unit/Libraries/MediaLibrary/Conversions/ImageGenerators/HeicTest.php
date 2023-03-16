<?php

namespace Tests\Unit\Libraries\MediaLibrary\Conversions\ImageGenerators;

use App\Libraries\MediaLibrary\Conversions\ImageGenerators\Heic;
use Imagick;
use ImagickException;
use ImagickPixel;
use Storage;
use Tests\TestCase;

class HeicTest extends TestCase
{
    /** @test */
    public function its_requirements_are_fulfilled()
    {
        $imageGenerator = new Heic();

        $this->assertTrue($imageGenerator->requirementsAreInstalled());
    }

    /** @test */
    public function it_support_files_with_heic_extension()
    {
        $imageGenerator = new Heic();

        $this->assertContains('heic', $imageGenerator->supportedExtensions());
    }

    /** @test */
    public function it_support_files_with_heic_mime_type()
    {
        $imageGenerator = new Heic();

        $this->assertContains('image/heic', $imageGenerator->supportedMimeTypes());
    }

    /** @test
     * @throws ImagickException
     */
    public function it_converts_heic_images_to_jpg()
    {
        Storage::fake();
        $imageGenerator = new Heic();
        $image          = new Imagick();
        $convertedImage = new Imagick();

        $image->newImage(100, 100, new ImagickPixel('red'), 'heic');

        Storage::put('test_image.heic', $image->getImageBlob());
        $imagePath          = Storage::path('test_image.heic');
        $convertedImagePath = Storage::path('test_image.' . Heic::FORMAT_OUTPUT);
        $imageGenerator->convert($imagePath);
        $convertedImage->readImage($convertedImagePath);

        $this->assertEquals('JPEG', $convertedImage->getImageFormat());
    }
}
