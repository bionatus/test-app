<?php

namespace App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\Call;

use App;
use App\Http\Controllers\Controller;
use App\Services\Communication\Auth\Providers\Twilio\Call\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FallbackController extends Controller
{
    public function __invoke(Response $providerResponse)
    {
        return \Response::make($providerResponse->technicalDifficulties())
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
