<?php

namespace Tests\Unit\Models;

use App\Models\TicketReview;

class TicketReviewTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(TicketReview::tableName(), [
            'id',
            'ticket_id',
            'agent_id',
            'rating',
            'comment',
            'created_at',
            'updated_at',
        ]);
    }
}
