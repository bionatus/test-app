<?php

namespace Tests\Unit\Models;

use App\Models\Status;
use App\Models\Substatus;

class SubstatusTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Substatus::tableName(), [
            'id',
            'status_id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_is_pending()
    {
        $statusPending       = Status::find(Status::STATUS_PENDING);
        $statusNotPending    = Status::find(Status::STATUS_APPROVED);
        $substatusNotPending = Substatus::factory()->usingStatus($statusNotPending)->create();
        $substatusPending    = Substatus::factory()->usingStatus($statusPending)->create();

        $this->assertFalse($substatusNotPending->isPending());
        $this->assertTrue($substatusPending->isPending());
    }

    /** @test */
    public function it_knows_if_is_pending_approval()
    {
        $statusPendingApproval       = Status::find(Status::STATUS_PENDING_APPROVAL);
        $statusNotPendingApproval    = Status::find(Status::STATUS_APPROVED);
        $substatusNotPendingApproval = Substatus::factory()->usingStatus($statusNotPendingApproval)->create();
        $substatusPendingApproval    = Substatus::factory()->usingStatus($statusPendingApproval)->create();

        $this->assertFalse($substatusNotPendingApproval->isPendingApproval());
        $this->assertTrue($substatusPendingApproval->isPendingApproval());
    }

    /** @test */
    public function it_knows_if_is_approved()
    {
        $statusApproved       = Status::find(Status::STATUS_APPROVED);
        $statusNotApproved    = Status::find(Status::STATUS_PENDING);
        $substatusNotApproved = Substatus::factory()->usingStatus($statusNotApproved)->create();
        $substatusApproved    = Substatus::factory()->usingStatus($statusApproved)->create();

        $this->assertFalse($substatusNotApproved->isApproved());
        $this->assertTrue($substatusApproved->isApproved());
    }

    /** @test */
    public function it_knows_if_is_canceled()
    {
        $statusCanceled       = Status::find(Status::STATUS_CANCELED);
        $statusNotCanceled    = Status::find(Status::STATUS_APPROVED);
        $substatusNotCanceled = Substatus::factory()->usingStatus($statusNotCanceled)->create();
        $substatusCanceled    = Substatus::factory()->usingStatus($statusCanceled)->create();

        $this->assertFalse($substatusNotCanceled->isCanceled());
        $this->assertTrue($substatusCanceled->isCanceled());
    }

    /** @test */
    public function it_knows_if_is_completed()
    {
        $statusCompleted       = Status::find(Status::STATUS_COMPLETED);
        $statusNotCompleted    = Status::find(Status::STATUS_APPROVED);
        $substatusNotCompleted = Substatus::factory()->usingStatus($statusNotCompleted)->create();
        $substatusCompleted    = Substatus::factory()->usingStatus($statusCompleted)->create();

        $this->assertFalse($substatusNotCompleted->isCompleted());
        $this->assertTrue($substatusCompleted->isCompleted());
    }

    /** @test */
    public function it_knows_the_status_name()
    {
        $status    = Status::find(Status::STATUS_PENDING);
        $substatus = Substatus::factory()->usingStatus($status)->create();
        $expected  = Status::STATUS_NAME_PENDING;
        $this->assertSame($expected, $substatus->getStatusName());
    }
}
