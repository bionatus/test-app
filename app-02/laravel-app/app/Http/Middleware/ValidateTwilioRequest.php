<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Config;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Twilio\Security\RequestValidator;

class ValidateTwilioRequest
{
    const TWILIO_HEADER_NAME = 'X-Twilio-Signature';

    public function handle(Request $request, Closure $next)
    {
        $requestValidator = new RequestValidator(Config::get('twilio.auth_token'));

        $requestData = [];
        if ($request->method() === 'POST') {
            $requestData = $request->toArray();
        }
        if (!empty($requestData['bodySHA256'])) {
            $requestData = $request->getContent();
        }

        $header = $request->header(self::TWILIO_HEADER_NAME, '');

        $isValid = $requestValidator->validate($header, $request->fullUrl(), $requestData);

        if (!$isValid) {
            return Response::noContent(HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
