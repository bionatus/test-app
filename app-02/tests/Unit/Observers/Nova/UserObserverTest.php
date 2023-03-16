<?php

namespace Tests\Unit\Observers\Nova;

use App\Models\User;
use App\Nova\Observers\UserObserver;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider dataProvider
     */
    public function it_sets_hat_requested_field_to_true_if_conditions_are_met(
        ?bool $hatRequestedOldValue,
        ?CarbonInterface $verifiedAtOldValue,
        ?CarbonInterface $verifiedAtNewValue,
        ?bool $hatRequestedExpected
    ) {
        $user = User::factory()->verified($verifiedAtOldValue)->create(['hat_requested' => $hatRequestedOldValue]);

        $user->verified_at = $verifiedAtNewValue;

        $observer = new UserObserver();
        $observer->updating($user);

        $this->assertSame($hatRequestedExpected, $user->hat_requested);
    }

    public function dataProvider(): array
    {
        return [
            // hatRequestedOldValue, verifiedAtOldValue, verifiedAtNewValue, hatRequestedExpected
            [null, null, Carbon::now(), true],
            [null, Carbon::now(), null, null],
            [null, Carbon::now(), Carbon::now(), null],
            [null, null, null, null],
            [false, null, Carbon::now(), false],
            [false, Carbon::now(), null, false],
            [false, Carbon::now(), Carbon::now(), false],
            [false, null, null, false],
            [true, null, Carbon::now(), true],
            [true, Carbon::now(), null, true],
            [true, Carbon::now(), Carbon::now(), true],
            [true, null, null, true],
        ];
    }
}
