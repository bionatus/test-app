<?php

namespace Database\Seeders;

interface EnvironmentSeeder
{
    public function environments(): array;

    public function canRunInEnvironment(): bool;

    public function run();
}
