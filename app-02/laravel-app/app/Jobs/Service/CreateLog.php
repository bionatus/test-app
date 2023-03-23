<?php

namespace App\Jobs\Service;

use App;
use App\Handlers\ServiceLogHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string     $serviceName;
    private Collection $request;
    private Collection $response;
    private ?Model     $model;

    public function __construct(string $serviceName, Collection $request, Collection $response, ?Model $model)
    {
        $this->onConnection('database');

        $this->serviceName = $serviceName;
        $this->request     = $request;
        $this->response    = $response;
        $this->model       = $model;
    }

    public function handle()
    {
        $serviceLogHandler = App::make(ServiceLogHandler::class, ['serviceName' => $this->serviceName]);
        $serviceLogHandler->log($this->request, $this->response, $this->model);
    }
}
