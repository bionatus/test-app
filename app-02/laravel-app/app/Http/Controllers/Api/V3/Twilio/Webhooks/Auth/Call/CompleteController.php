<?php

namespace App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\Call;

use App;
use App\Http\Controllers\Controller;
use Exception;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CompleteController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke()
    {
        return Response::noContent()->setStatusCode(SymfonyResponse::HTTP_NO_CONTENT);
    }
}
