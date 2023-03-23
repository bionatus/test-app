<?php

namespace Tests\Unit\Policies\Api\V2\Ticket;

use App\Models\Agent;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\Api\V2\TicketPolicy;
use Mockery;
use PHPUnit\Framework\TestCase;

class CloseTest extends TestCase
{
    /**
     * @test
     * @dataProvider closeProvider
     */
    public function it_allows_specific_conditions_to_close_it(
        bool $expected,
        bool $isOwner,
        bool $isAgent,
        bool $isParticipant
    ) {
        $user = Mockery::mock(User::class);
        $user->makePartial();

        $ticket = Mockery::mock(Ticket::class);
        $ticket->shouldReceive('isOwner')->withArgs([$user])->once()->andReturn($isOwner);

        $user->shouldReceive('isAgent')->times((int) !$isOwner)->andReturn($isAgent);

        $user->shouldReceive('getAttribute')
            ->withArgs(['agent'])
            ->times((int) (!$isOwner && $isAgent))
            ->andReturn($agent = new Agent());
        $ticket->shouldReceive('isActiveParticipant')
            ->withArgs([$agent])
            ->times((int) (!$isOwner && $isAgent))
            ->andReturn($isParticipant);

        $policy = new TicketPolicy();

        $this->assertSame($expected, $policy->close($user, $ticket));
    }

    public function closeProvider(): array
    {
        return [
            '!owner, !agent, !participant' => [false, false, false, false],
            '!owner, !agent, participant'  => [false, false, false, true],
            '!owner, agent, !participant'  => [false, false, true, false],
            '!owner, agent, participant'   => [true, false, true, true],
            'owner, !agent, !participant'  => [true, true, false, false],
            'owner, !agent, participant'   => [true, true, false, true],
            'owner, agent, !participant'   => [true, true, true, false],
            'owner, agent, participant'    => [true, true, true, true],
        ];
    }
}
