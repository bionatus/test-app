<?php

namespace App\Libraries\MediaLibrary\Conversions\ImageGenerators;

use Illuminate\Support\Collection;
use Imagick;
use ImagickException;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class Heic extends ImageGenerator
{
    const FORMAT_OUTPUT = 'jpg';

    /**
     * @throws ImagickException
     */
    public function convert(string $file, Conversion $conversion = null): string
    {
        $pathToImage      = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME);
        $pathToOriginal   = $pathToImage . '.' . pathinfo($file, PATHINFO_EXTENSION);
        $pathToConversion = $pathToImage . '.' . self::FORMAT_OUTPUT;

        $image = new Imagick();
        $image->readImage($pathToOriginal);
        $image->setFormat(self::FORMAT_OUTPUT);

        $image->writeImage($pathToConversion);

        $image->destroy();

        return $pathToConversion;
    }

    public function requirementsAreInstalled(): bool
    {
        return extension_loaded('imagick');
    }

    public function supportedExtensions(): Collection
    {
        return Collection::make(['heic']);
    }

    public function supportedMimeTypes(): Collection
    {
        return Collection::make('image/heic');
    }
}
