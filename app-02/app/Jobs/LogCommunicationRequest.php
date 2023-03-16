<?php

namespace App\Jobs;

use App\Models\Communication;
use App\Models\CommunicationLog;
use Config;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogCommunicationRequest
{
    use Dispatchable, SerializesModels;

    private Communication $communication;
    private array         $payload;
    private string        $response;
    private array         $errors;
    private bool          $enabled;
    private ?string       $source;
    private string        $description;

    public function __construct(
        Communication $communication,
        string $description,
        array $payload,
        string $response,
        ?string $source,
        array $errors = []
    ) {
        $this->communication = $communication;
        $this->description   = $description;
        $this->payload       = $payload;
        $this->response      = $response;
        $this->errors        = $errors;
        $this->source        = $source;
        $this->enabled       = !!Config::get('communications.log_requests');
    }

    public function handle(): void
    {
        if (!$this->enabled) {
            return;
        }

        $communicationLog = new CommunicationLog([
            'description' => $this->description,
            'request'     => $this->payload,
            'response'    => $this->response,
            'source'      => $this->source,
            'errors'      => $this->errors,
        ]);

        $this->communication->logs()->save($communicationLog);
    }
}
