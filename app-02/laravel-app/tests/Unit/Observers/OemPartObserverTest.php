<?php

namespace Tests\Unit\Observers;

use App\Models\OemPart;
use App\Observers\OemPartObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OemPartObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uid_when_creating()
    {
        $oemPart = OemPart::factory()->make(['uid' => null]);

        $observer = new OemPartObserver();

        $observer->creating($oemPart);

        $this->assertNotNull($oemPart->uid);
    }

    /** @test */
    public function it_does_not_change_uid_when_not_empty()
    {
        $oemPart = OemPart::factory()->make(['uid' => $uid = '123456']);

        $observer = new OemPartObserver();

        $observer->creating($oemPart);

        $this->assertSame($uid, $oemPart->uid);
    }
}
