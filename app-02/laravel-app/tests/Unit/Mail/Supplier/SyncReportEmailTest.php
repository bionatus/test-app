<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\SyncReportEmail;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use ReflectionClass;
use ReflectionException;
use Str;
use Tests\TestCase;

class SyncReportEmailTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SyncReportEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function it_builds_a_sync_report_email()
    {
        $now               = CarbonImmutable::now();
        $tomorrow          = $now->addDay();
        $processedRecords  = 123456789;
        $malformedRecords  = 987654321;
        $createdIds        = Collection::make(array_fill(0, 111, ''));
        $updatedIds        = Collection::make(array_fill(0, 222, ''));
        $deletedIds        = Collection::make(array_fill(0, 333, ''));
        $failedGeocodesIds = Collection::make(array_fill(0, 55, ''));
        $errors            = Collection::make([
            $id = '444'                => new MessageBag([$error = 'An error occurred.']),
            $unknownId = 'unknown 555' => new MessageBag([$unknownError = 'Another error occurred.']),
        ]);
        $warnings          = Collection::make([
            $warningId = '555' => new MessageBag([$warning = 'A warning.']),
        ]);

        $mailable = new SyncReportEmail($now, $tomorrow, $processedRecords, $createdIds, $updatedIds, $deletedIds,
            $failedGeocodesIds, $errors, $warnings, $malformedRecords);

        $render = $mailable->render();
        $this->assertTrue(Str::contains($render, $now->toIso8601String()));
        $this->assertTrue(Str::contains($render, $tomorrow->toIso8601String()));
        $this->assertTrue(Str::contains($render, $processedRecords));
        $this->assertTrue(Str::contains($render, $malformedRecords));
        $this->assertTrue(Str::contains($render, $createdIds->count()));
        $this->assertTrue(Str::contains($render, $updatedIds->count()));
        $this->assertTrue(Str::contains($render, $deletedIds->count()));
        $this->assertTrue(Str::contains($render, $failedGeocodesIds->count()));
        $this->assertTrue(Str::contains($render, 'Errors:'));
        $this->assertTrue(Str::contains($render, $id));
        $this->assertTrue(Str::contains($render, $error));
        $this->assertTrue(Str::contains($render, $unknownId));
        $this->assertTrue(Str::contains($render, $unknownError));
        $this->assertTrue(Str::contains($render, 'Warnings:'));
        $this->assertTrue(Str::contains($render, $warningId));
        $this->assertTrue(Str::contains($render, $warning));
    }
}
