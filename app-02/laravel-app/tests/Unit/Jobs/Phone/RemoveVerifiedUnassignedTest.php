<?php

namespace Tests\Unit\Jobs\Phone;

use App\Jobs\Phone\RemoveVerifiedUnassigned;
use App\Models\Phone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class RemoveVerifiedUnassignedTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemoveVerifiedUnassigned::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new RemoveVerifiedUnassigned(new Phone());

        $this->assertSame('database', $job->connection);
    }

    /** @test
     * @dataProvider verifiedAssignedProvider
     */
    public function it_deletes_it_if_is_verified_and_not_assigned(bool $isVerified, bool $isAssigned, bool $expected)
    {
        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('isVerified')->withNoArgs()->once()->andReturn($isVerified);
        $phone->shouldReceive('isAssigned')->withNoArgs()->times((int) $isVerified)->andReturn($isAssigned);
        $phone->shouldReceive('delete')
            ->withNoArgs()
            ->times((int) $isVerified * (int) !$isAssigned)
            ->andReturn($expected);

        $job = new RemoveVerifiedUnassigned($phone);
        $job->handle();

        $this->assertTrue(true);
    }

    public function verifiedAssignedProvider(): array
    {
        return [
            '!verified !assigned' => [false, false, false],
            '!verified assigned'  => [false, true, false],
            'verified !assigned'  => [true, false, true],
            'verified assigned'   => [true, true, false],
        ];
    }
}
