<?php

namespace Tests\Unit\Console\Commands;

use App;
use App\Exports\OrderInvoicesExport;
use App\Jobs\OrderInvoice\SendExportEmail;
use App\Jobs\OrderInvoice\ToProcess;
use Bus;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class ExportOrderInvoicesCommandTest extends TestCase
{
    /** @test */
    public function it_dispatches_invoices_csv_job_and_calls_to_send_it_via_email()
    {
        Bus::fake();

        Carbon::setTestNow('2022-06-08 00:00:00');

        $sendExportEmail = new SendExportEmail('fake type', 'fake filePath', 'fake email subject');
        App::bind(SendExportEmail::class, fn() => $sendExportEmail);

        $toProcess = new ToProcess();
        App::bind(ToProcess::class, fn() => $toProcess);

        $queue = Mockery::mock(PendingDispatch::class);
        $queue->shouldReceive('chain')->withArgs([[$sendExportEmail, $toProcess]])->once()->andReturnSelf();

        $export = Mockery::mock(OrderInvoicesExport::class);
        $export->shouldReceive('queue')
            ->withArgs(['invoices-2022-05.csv', 'exports', 'Csv'])
            ->once()
            ->andReturn($queue);
        App::bind(OrderInvoicesExport::class, fn() => $export);

        $this->artisan('export:invoices')->assertSuccessful();
    }
}
