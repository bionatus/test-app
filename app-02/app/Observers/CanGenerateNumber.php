<?php

namespace App\Observers;

trait CanGenerateNumber
{
    protected function generateStringNumber(int $digits = 6): string
    {
        $number = '';
        for ($i = 0; $i < $digits; $i += 1) {
            $number .= $this->randomDigit();
        }

        return $number;
    }

    protected function randomDigit(): int
    {
        return rand(1, 9);
    }
}
