<?php

namespace App\Services\Communication\Auth\Call;

interface ResponseInterface
{
    public function sayCode(string $code): string;

    public function hangUp(): string;

    public function technicalDifficulties(): string;
}
