<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ValidatePointsOnSupportCall;
use App\Models\User;
use Auth;
use Exception;
use Mockery;
use Request;
use Tests\TestCase;

class ValidatePointsOnSupportCallTest extends TestCase
{
    /** @test
     * @@dataProvider infoProvider
     * @throws Exception
     */
    public function it_checks_if_user_can_make_a_support_call(
        bool $isSupportCallDisabled,
        int $points,
        bool $success,
        string $msg = null
    ) {
        $userMock = Mockery::mock(User::class);
        $userMock->shouldReceive('isSupportCallDisabled')->andReturn($isSupportCallDisabled);
        $userMock->shouldReceive('totalPointsEarned')->andReturn($points);

        Auth::shouldReceive('user')->once()->andreturn($userMock);

        $request = Request::instance();

        $middleware = new ValidatePointsOnSupportCall();

        if ($success) {
            $this->assertTrue($middleware->handle($request, fn() => true));
        }

        if (!$success) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($msg);
            $middleware->handle($request, fn() => null);
        }
    }

    public function infoProvider(): array
    {
        return [
            [true, 999, false, 'Support call disabled.'],
            [true, 1000, true],
            [false, 999, true],
            [false, 1000, true],
        ];
    }
}
