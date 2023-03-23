<?php

namespace App\Exceptions;

use App;
use App\Constants\RoutePrefixes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash  = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e)
    {
        if (App::bound('sentry') && $this->shouldReport($e)) {
            App::make('sentry')->captureException($e);
        }

        parent::report($e);
    }

    public function render($request, Throwable $e)
    {
        $segments     = $request->segments();
        $firstSegment = $segments[0] ?? null;

        if (in_array($firstSegment, [RoutePrefixes::API, RoutePrefixes::LIVE])) {
            $request->headers->set('Accept', 'application/json');
        }

        if ($e instanceof ModelNotFoundException) {
            abort(404, 'No results found.');
        }

        return parent::render($request, $e);
    }
}
