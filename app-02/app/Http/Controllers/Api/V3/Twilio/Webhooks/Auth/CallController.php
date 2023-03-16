<?php

namespace App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Twilio\Webhooks\Auth\Call\InvokeRequest;
use App\Services\Communication\Auth\Providers\Twilio\Call\Response;
use Exception;
use NumberFormatter;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CallController extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(InvokeRequest $request, Response $providerResponse)
    {
        $authenticationCode = $request->authenticationCode();

        $code = '';
        foreach (str_split($authenticationCode->code) as $digit) {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);

            $code .= $formatter->format($digit) . ',';
        }

        $response = $providerResponse->sayCode($code);

        return \Response::make($response)->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
