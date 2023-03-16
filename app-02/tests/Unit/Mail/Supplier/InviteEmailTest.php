<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\InviteEmail;
use App\Models\Supplier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class InviteEmailTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(InviteEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new InviteEmail(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'Name');

        $mailable = new InviteEmail($supplier);
        $mailable->assertSeeInHtml($name);
    }
}
