<?php

namespace Tests\Unit\Policies\Api\V2\Ticket;

use App\Models\Agent;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\Api\V2\TicketPolicy;
use Mockery;
use PHPUnit\Framework\TestCase;

class AgentRateTest extends TestCase
{
    /** @test
     * @dataProvider rateProvider()
     */
    public function it_allows_specific_conditions_to_rate_it(
        bool $expected,
        bool $isClosed,
        bool $isAgent,
        bool $isParticipant
    ) {
        $ticket = Mockery::mock(Ticket::class);

        $ticket->shouldReceive('isClosed')->withNoArgs()->once()->andReturn($isClosed);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAgent')->withNoArgs()->times((int) $isClosed)->andReturn($isAgent);
        $user->shouldReceive('getAttribute')
            ->withArgs(['agent'])
            ->times((int) ($isClosed && $isAgent))
            ->andReturn($agent = new Agent());
        $ticket->shouldReceive('isActiveParticipant')
            ->withArgs([$agent])
            ->times((int) ($isClosed && $isAgent))
            ->andReturn($isParticipant);
        $policy = new TicketPolicy();

        $this->assertSame($expected, $policy->agentRate($user, $ticket));
    }

    public function rateProvider(): array
    {
        return [
            '!closed !agent !participant' => [false, false, false, false],
            '!closed !agent participant'  => [false, false, false, true],
            '!closed agent !participant'  => [false, false, true, false],
            '!closed agent participant'   => [false, false, true, true],
            'closed !agent !participant'  => [false, true, false, false],
            'closed !agent participant'   => [false, true, false, true],
            'closed agent !participant'   => [false, true, true, false],
            'closed agent participant'    => [true, true, true, true],
        ];
    }
}
