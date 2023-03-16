<?php

namespace App\Types;

use Exception;
use Throwable;

class Location
{
    const MAX_LATITUDE  = 90;
    const MIN_LATITUDE  = -90;
    const MAX_LONGITUDE = 180;
    const MIN_LONGITUDE = -180;
    private string $latitude;
    private string $longitude;

    /**
     * @param string $string
     *
     * @return static
     * @throws Throwable
     */
    public static function createFromString(string $string): self
    {
        throw_unless(self::isValidStringFormat($string), Exception::class);

        $elements = explode(',', $string);

        return new Location($elements[0], $elements[1]);
    }

    public static function isValidStringFormat(string $string): bool
    {
        $elements = explode(',', $string);

        return 2 === count($elements);
    }

    /**
     * @param string $string
     *
     * @return string
     * @throws Throwable
     */
    private static function latitudeFromString(string $string): string
    {
        throw_unless(self::isValidStringFormat($string), Exception::class);

        $elements = explode(',', $string);

        return $elements[0];
    }

    public static function isValidLatitude(string $string): bool
    {
        try {
            $latitude = self::latitudeFromString($string);
        } catch (Throwable $e) {
            return false;
        }

        if (!is_numeric($latitude)) {
            return false;
        }

        return !(Location::MIN_LATITUDE > $latitude || Location::MAX_LATITUDE < $latitude);
    }

    /**
     * @param string $string
     *
     * @return string
     * @throws Throwable
     */
    private static function longitudeFromString(string $string): string
    {
        throw_unless(self::isValidStringFormat($string), Exception::class);

        $elements = explode(',', $string);

        return $elements[1];
    }

    public static function isValidLongitude(string $string): bool
    {
        try {
            $longitude = self::longitudeFromString($string);
        } catch (Throwable $e) {
            return false;
        }

        if (!is_numeric($longitude)) {
            return false;
        }

        return !(Location::MIN_LONGITUDE > $longitude || Location::MAX_LONGITUDE < $longitude);
    }

    /**
     * @param string $latitude
     * @param string $longitude
     *
     * @throws Exception
     */
    public function __construct(string $latitude, string $longitude)
    {
        if (!$this->areValid($latitude, $longitude)) {
            throw new Exception();
        }

        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    private function areValid(string $latitude, string $longitude)
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        if (self::MIN_LATITUDE > $latitude || self::MAX_LATITUDE < $latitude) {
            return false;
        }

        if (self::MIN_LONGITUDE > $longitude || self::MAX_LONGITUDE < $longitude) {
            return false;
        }

        return true;
    }

    public function latitude(): string
    {
        return $this->latitude;
    }

    public function longitude(): string
    {
        return $this->longitude;
    }

    public function __toString(): string
    {
        return implode(',', [$this->latitude, $this->longitude]);
    }
}
