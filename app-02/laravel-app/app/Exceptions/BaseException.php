<?php

namespace App\Exceptions;

use App\Constants\RoutePrefixes;
use Exception;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseException extends Exception
{
    public function render($request)
    {
        $segments     = $request->segments();
        $firstSegment = $segments[0] ?? null;

        if (in_array($firstSegment, [RoutePrefixes::API, RoutePrefixes::LIVE])) {
            $request->headers->set('Accept', 'application/json');
        }

        abort(Response::HTTP_FAILED_DEPENDENCY, $this->message);
    }
}
