<?php

namespace App\Actions\Models\Activity;

use App\Actions\Models\Activity\Contracts\Executable;
use Illuminate\Database\Eloquent\Model;

class BuildResource implements Executable
{
    protected Model  $model;
    protected string $resource;

    public function __construct(Model $model, string $resource)
    {
        $this->model    = $model;
        $this->resource = $resource;
    }

    public function execute(): array
    {
        return json_decode((new $this->resource($this->model))->toJson(), true);
    }
}
