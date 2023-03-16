<?php

namespace App\Types;

class Point
{
    private int   $points;
    private float $coefficient;
    private int   $multiplier;

    public function __construct(int $points, float $coefficient, int $multiplier)
    {
        $this->points      = $points;
        $this->coefficient = $coefficient;
        $this->multiplier  = $multiplier;
    }

    public function points(): int
    {
        return $this->points;
    }

    public function coefficient(): float
    {
        return $this->coefficient;
    }

    public function multiplier(): int
    {
        return $this->multiplier;
    }
}
