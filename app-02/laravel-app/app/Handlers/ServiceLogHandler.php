<?php

namespace App\Handlers;

use App\Models\ServiceLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ServiceLogHandler
{
    protected string $name;

    public function __construct(string $serviceName)
    {
        $this->name = $serviceName;
    }

    public function log(Collection $request, Collection $response, ?Model $model): ServiceLog
    {
        $causerId = $causerType = null;

        if ($model) {
            $causerId   = $model->getKey();
            $causerType = $model->getMorphClass();
        }

        return $this->createLog($causerId, $causerType, $request->get('method'), $request->get('url'),
            json_encode($request->get('payload')), $response->get('status'), $response->get('content'));
    }

    private function createLog(
        ?int $causerId,
        ?string $causerType,
        string $method,
        string $url,
        string $payload,
        int $responseStatus,
        string $responseContent
    ): ServiceLog {
        return ServiceLog::create([
            'causer_id'        => $causerId,
            'causer_type'      => $causerType,
            'name'             => $this->name,
            'request_method'   => $method,
            'request_url'      => $url,
            'request_payload'  => $payload,
            'response_status'  => $responseStatus,
            'response_content' => $responseContent,
        ]);
    }
}
