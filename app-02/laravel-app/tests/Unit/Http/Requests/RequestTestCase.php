<?php

namespace Tests\Unit\Http\Requests;

use MohammedManssour\FormRequestTester\TestsFormRequests;
use Str;
use Tests\TestCase;

abstract class RequestTestCase extends TestCase
{
    use TestsFormRequests;

    protected string $requestClass;

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)->assertAuthorized();
    }

    protected function getDisplayableAttribute(string $attribute)
    {
        return Str::of($attribute)->replace('_', ' ');
    }
}
