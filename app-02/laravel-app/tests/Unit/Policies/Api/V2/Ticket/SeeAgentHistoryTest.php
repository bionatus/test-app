<?php

namespace Tests\Unit\Policies\Api\V2\Ticket;

use App\Models\User;
use App\Policies\Api\V2\TicketPolicy;
use Mockery;
use PHPUnit\Framework\TestCase;

class SeeAgentHistoryTest extends TestCase
{
    /** @test
     * @dataProvider rateProvider()
     */
    public function it_allows_specific_conditions_to_rate_it(bool $expected, bool $isAgent)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAgent')->withNoArgs()->once()->andReturn($isAgent);
        $policy = new TicketPolicy();

        $this->assertSame($expected, $policy->seeAgentHistory($user));
    }

    public function rateProvider(): array
    {
        return [
            '!agent' => [false, false],
            'agent'  => [true, true],
        ];
    }
}
