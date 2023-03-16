<?php

namespace App\Lib;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class AirtableClient {

    protected $initialized;

    protected $client;
    protected $requestsLimit;
    protected $requestsTimeLimit;
    protected $headers;

    protected $endpoint;
    protected $offset;

    protected $failed;

    /**
     * Setups the Airtable Client instance
     *
     * @param   string $endpoint
     * @return  void
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('airtable.url') . '/' .
                config('airtable.version') . '/' .
                config('airtable.table') . '/'
        ]);
        $this->requestsLimit = config('airtable.requests_limit');
        $this->requestsTimeLimit = config('airtable.requests_time_limit');
        $this->headers = [ 'Authorization' => 'Bearer ' . config('airtable.token') ];
    }

    /**
     * Sets the client targeted endpoint
     *
     * @param   string $endpoint
     * @return  void
     */
    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Checks the current failed state of the client
     *
     * @return  void
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * Handles the preparation of hte airtable request prerequisites
     *
     * @param  array         $parseRequestResults
     * @param  int|integer   $subsequent
     * @param  float|integer $timeElapsed
     * @return void
     */
    public function prepare(
        array $parseRequestResults,
        int $subsequent = 1,
        float $timeElapsed = 0
    )
    {
        if (!$this->endpoint) {
            return;
        }

        if ($subsequent >= $this->requestsLimit) {
            if ($timeElapsed <= $this->requestsTimeLimit) {
                $sleep = ($this->requestsTimeLimit - $timeElapsed) * 100000;

                usleep($sleep);
            }

            $timeElapsed = 0;
            $subsequent = 1;
        }

        $start = microtime(true);

        $results = $this->request();

        call_user_func($parseRequestResults, Arr::get($results, 'records'));

        $this->offset = Arr::get($results, 'offset');

        $results = null;

        if (!$this->offset) {
            return;
        }

        $elapsed = microtime(true) - $start;

        return $this->prepare(
            $parseRequestResults,
            $subsequent+1,
            $timeElapsed+$elapsed
        );
    }

    /**
     * Executes the Airtable request
     *
     * @return void
     */
    protected function request()
    {
        $params = [
            'pageSize' => config('airtable.results_per_request')
        ];

        if ($this->offset) {
            $params['offset'] = $this->offset;
        }

        $response = $this->client->request('GET', $this->endpoint, [
            'headers' => $this->headers,
            'query' => $params
        ] );

        if ($response->getStatusCode() !== 200) {
            $this->failed = true;

            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
