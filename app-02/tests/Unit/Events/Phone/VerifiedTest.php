<?php

namespace Tests\Unit\Events\Phone;

use App\Events\Phone\Verified;
use App\Listeners\Phone\DelayRemoveVerifiedUnassignedJob;
use App\Models\Phone;
use Tests\TestCase;

class VerifiedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Verified::class, [
            DelayRemoveVerifiedUnassignedJob::class,
        ]);
    }

    /** @test */
    public function it_returns_its_phone()
    {
        $phone = new Phone();

        $event = new Verified($phone);

        $this->assertSame($phone, $event->phone());
    }
}
