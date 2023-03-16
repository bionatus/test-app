<?php

namespace App\Actions\Models\Activity;

use App\Actions\Models\Activity\Contracts\Executable;

class BuildProperty implements Executable
{
    protected string $key   = '';
    protected string $value = '';

    public function __construct(string $key, string $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function execute(): array
    {
        return [$this->key => $this->value];
    }
}
