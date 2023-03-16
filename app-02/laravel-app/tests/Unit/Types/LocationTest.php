<?php

namespace Tests\Unit\Types;

use App\Types\Location;
use Exception;
use Tests\TestCase;
use Throwable;

class LocationTest extends TestCase
{

    /** @test
     *
     * @dataProvider instanceProvider
     *
     * @param string $latitude
     * @param string $longitude
     * @param bool   $valid
     *
     * @throws Throwable
     */
    public function it_needs_valid_latitude_and_longitude_to_instance_from_string(string $latitude, string $longitude, bool $valid)
    {
        if (!$valid) {
            $this->expectException(Exception::class);
        }

        $location = Location::createFromString("${latitude},${longitude}");

        if ($valid) {
            $this->assertInstanceOf(Location::class, $location);
        }
    }

    /** @test
     *
     * @dataProvider instanceProvider
     *
     * @param string $latitude
     * @param string $longitude
     * @param bool   $valid
     */
    public function it_needs_valid_latitude_and_longitude_to_instance(string $latitude, string $longitude, bool $valid)
    {
        if (!$valid) {
            $this->expectException(Exception::class);
        }

        $location = new Location($latitude, $longitude);

        if ($valid) {
            $this->assertInstanceOf(Location::class, $location);
        }
    }

    public function instanceProvider(): array
    {
        return [
            'empty, empty, '                               => ['', '', false],
            'empty, invalid, '                             => ['', 'invalid', false],
            'empty, numeric out of range, '                => ['', '181', false],
            'empty, valid, '                               => ['', '90', false],
            'invalid, empty, '                             => ['invalid', '', false],
            'invalid, invalid, '                           => ['invalid', 'invalid', false],
            'invalid, numeric out of range, '              => ['invalid', '181', false],
            'invalid, valid, '                             => ['invalid', '90', false],
            'numeric out of range, empty, '                => ['91', '', false],
            'numeric out of range, invalid, '              => ['91', 'invalid', false],
            'numeric out of range, numeric out of range, ' => ['91', '181', false],
            'numeric out of range, valid, '                => ['91', '90', false],
            'valid, empty, '                               => ['10', '', false],
            'valid, invalid, '                             => ['10', 'invalid', false],
            'valid, numeric out of range, '                => ['10', '181', false],
            'valid, valid, '                               => ['10', '90', true],
        ];
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_latitude()
    {
        $latitude = '15';

        $location = new Location($latitude, 10);

        $this->assertSame($latitude, $location->latitude());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_longitude()
    {
        $longitude = '15';

        $location = new Location(10, $longitude);

        $this->assertSame($longitude, $location->longitude());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_string_representation()
    {
        $location = new Location(10, 20);

        $this->assertSame('10,20', $location->__toString());
        $this->assertSame('10,20', (string) $location);
    }

    /** @test
     *
     * @param string $string
     * @param bool   $expected
     *
     * @dataProvider stringProvider
     */
    public function it_validates_string_format(string $string, bool $expected)
    {
        $this->assertSame($expected, Location::isValidStringFormat($string));
    }

    public function stringProvider(): array
    {
        return [
            ['', false],
            ['invalid', false],
            ['0', false],
            [',', true],
            ['0,0', true],
            ['0,0,', false],
        ];
    }

    /** @test
     *
     * @param string $string
     * @param bool   $expected
     *
     * @dataProvider latitudeStringProvider
     */
    public function it_validates_latitude_from_string(string $string, bool $expected)
    {
        $this->assertSame($expected, Location::isValidLatitude($string));
    }

    public function latitudeStringProvider(): array
    {
        return [
            ['', false],
            ['invalid', false],
            ['0', false],
            [',', false],
            ['-91,', false],
            ['91,', false],
            ['0,', true],
        ];
    }

    /** @test
     *
     * @param string $string
     * @param bool   $expected
     *
     * @dataProvider longitudeStringProvider
     */
    public function it_validates_longitude_from_string(string $string, bool $expected)
    {
        $this->assertSame($expected, Location::isValidLongitude($string));
    }

    public function longitudeStringProvider(): array
    {
        return [
            ['', false],
            ['invalid', false],
            ['0', false],
            [',', false],
            [',-181', false],
            [',181', false],
            [',0', true],
        ];
    }
}
