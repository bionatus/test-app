<?php

namespace Tests\Unit\Types;

use App\Types\Coordinates;
use Exception;
use Tests\TestCase;

class CoordinatesTest extends TestCase
{
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

        $coordinates = new Coordinates($latitude, $longitude);

        if ($valid) {
            $this->assertInstanceOf(Coordinates::class, $coordinates);
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
        $latitude = 10;

        $coordinates = new Coordinates($latitude, 0);

        $this->assertEquals($latitude, $coordinates->latitude());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_longitude()
    {
        $longitude = 10;

        $coordinates = new Coordinates(0, $longitude);

        $this->assertEquals($longitude, $coordinates->longitude());
    }

    /** @test */
    public function it_knows_if_a_latitude_is_valid()
    {
        $this->assertFalse(Coordinates::isValidLatitude(''));
        $this->assertFalse(Coordinates::isValidLatitude(false));
        $this->assertFalse(Coordinates::isValidLatitude('invalid'));
        $this->assertFalse(Coordinates::isValidLatitude('-91'));
        $this->assertFalse(Coordinates::isValidLatitude('91'));
        $this->assertTrue(Coordinates::isValidLatitude('90'));
        $this->assertTrue(Coordinates::isValidLatitude('-90'));
        $this->assertTrue(Coordinates::isValidLatitude('0'));
    }

    /** @test */
    public function it_knows_if_a_longitude_is_valid()
    {
        $this->assertFalse(Coordinates::isValidLongitude(''));
        $this->assertFalse(Coordinates::isValidLongitude(false));
        $this->assertFalse(Coordinates::isValidLongitude('invalid'));
        $this->assertFalse(Coordinates::isValidLongitude('-181'));
        $this->assertFalse(Coordinates::isValidLongitude('181'));
        $this->assertTrue(Coordinates::isValidLongitude('180'));
        $this->assertTrue(Coordinates::isValidLongitude('-180'));
        $this->assertTrue(Coordinates::isValidLongitude('0'));
    }}
