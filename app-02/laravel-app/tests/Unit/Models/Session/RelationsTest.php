<?php

namespace Tests\Unit\Models\Session;

use App\Models\Communication;
use App\Models\CommunicationLog;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Session $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Session::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_ticket()
    {
        $session = Session::factory()->withTicket()->create();
        $related = $session->ticket()->first();

        $this->assertInstanceOf(Ticket::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_subject()
    {
        $session = Session::factory()->create();
        $related = $session->subject()->first();

        $this->assertInstanceOf(Subject::class, $related);
    }

    /** @test */
    public function it_has_communications()
    {
        Communication::factory()->usingSession($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->communications()->get();

        $this->assertCorrectRelation($related, Communication::class);
    }

    /** @test */
    public function it_has_communication_logs()
    {
        $communication = Communication::factory()->usingSession($this->instance)->create();
        CommunicationLog::factory()->usingCommunication($communication)->count(self::COUNT)->create();

        $related = $this->instance->communicationLogs()->get();

        $this->assertCorrectRelation($related, CommunicationLog::class);
    }
}
