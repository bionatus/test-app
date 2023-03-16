<?php

namespace Tests\Unit\Policies\Api\V2\Ticket;

use App\Models\Ticket;
use App\Models\User;
use App\Policies\Api\V2\TicketPolicy;
use Mockery;
use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    /** @test
     * @dataProvider rateProvider()
     */
    public function it_allows_specific_conditions_to_rate_it(bool $expected, bool $isOwner, bool $isClosed)
    {
        $ticket = Mockery::mock(Ticket::class);
        $user   = new User();
        $ticket->shouldReceive('isOwner')->withArgs([$user])->once()->andReturn($isOwner);
        $ticket->shouldReceive('isClosed')->withNoArgs()->times((int) $isOwner)->andReturn($isClosed);
        $policy = new TicketPolicy();

        $this->assertSame($expected, $policy->rate($user, $ticket));
    }

    public function rateProvider(): array
    {
        return [
            '!isOwner !isClosed' => [false, false, false],
            '!isOwner isClosed'  => [false, false, true],
            'isOwner !isClosed'  => [false, true, false],
            'isOwner isClosed'   => [true, true, true],
        ];
    }
}
