<?php

namespace App\Http\Middleware;

use Config;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $addHttpCookie = true;
    protected $except        = [];

    public function __construct(Application $app, Encrypter $encrypter)
    {
        $this->setExceptCSRFPaths();

        parent::__construct($app, $encrypter);
    }

    private function setExceptCSRFPaths(): void
    {
        $configPaths = Config::get('session.csrf_except_paths');

        $this->except = array_merge($this->except, $configPaths);
    }
}
