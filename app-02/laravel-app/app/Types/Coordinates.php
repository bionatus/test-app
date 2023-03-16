<?php

namespace App\Types;

use Exception;

class Coordinates
{
    const MAX_LATITUDE  = 90;
    const MIN_LATITUDE  = -90;
    const MAX_LONGITUDE = 180;
    const MIN_LONGITUDE = -180;
    private string $latitude;
    private string $longitude;

    public static function isValidLatitude($latitude): bool
    {
        if (!is_numeric($latitude)) {
            return false;
        }

        return self::MIN_LATITUDE <= $latitude && self::MAX_LATITUDE >= $latitude;
    }

    public static function isValidLongitude($longitude): bool
    {
        if (!is_numeric($longitude)) {
            return false;
        }

        return self::MIN_LONGITUDE <= $longitude && self::MAX_LONGITUDE >= $longitude;
    }

    /**
     * @throws Exception
     */
    public function __construct($latitude, $longitude)
    {
        if (!$this->areValid($latitude, $longitude)) {
            throw new Exception();
        }

        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    private function areValid(string $latitude, string $longitude)
    {
        return self::isValidLatitude($latitude) && self::isValidLongitude($longitude);
    }

    public function latitude(): string
    {
        return $this->latitude;
    }

    public function longitude(): string
    {
        return $this->longitude;
    }
}
