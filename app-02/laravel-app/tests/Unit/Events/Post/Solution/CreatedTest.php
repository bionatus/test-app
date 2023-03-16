<?php

namespace Tests\Unit\Events\Post\Solution;

use App\Events\Post\Solution\Created;
use App\Listeners\SendPostSolvedNotification;
use App\Listeners\SendSolutionCreatedNotification;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Created::class, [
            SendSolutionCreatedNotification::class,
            SendPostSolvedNotification::class,
        ]);
    }
}
