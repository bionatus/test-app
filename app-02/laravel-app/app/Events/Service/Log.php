<?php

namespace App\Events\Service;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class Log
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string     $serviceName;
    private Collection $request;
    private Collection $response;
    private ?Model     $model;

    public function __construct(string $serviceName, Collection $request, Collection $response, ?Model $model)
    {
        $this->serviceName = $serviceName;
        $this->request     = $request;
        $this->response    = $response;
        $this->model       = $model;
    }

    public function serviceName(): string
    {
        return $this->serviceName;
    }

    public function request(): Collection
    {
        return $this->request;
    }

    public function response(): Collection
    {
        return $this->response;
    }

    public function model(): ?Model
    {
        return $this->model;
    }
}
