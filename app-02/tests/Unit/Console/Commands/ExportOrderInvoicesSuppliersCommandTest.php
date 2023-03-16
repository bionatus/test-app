<?php

namespace Tests\Unit\Console\Commands;

use App;
use App\Exports\OrderInvoicesSuppliersExport;
use App\Jobs\OrderInvoice\SendExportEmail;
use Bus;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class ExportOrderInvoicesSuppliersCommandTest extends TestCase
{
    /** @test */
    public function it_dispatches_customers_csv_job_and_calls_to_send_it_via_email()
    {
        Bus::fake();

        Carbon::setTestNow('2022-06-08 00:00:00');

        $sendExportEmail = new SendExportEmail('fake type', 'fake filePath', 'fake email subject');
        App::bind(SendExportEmail::class, fn() => $sendExportEmail);

        $queue = Mockery::mock(PendingDispatch::class);
        $queue->shouldReceive('chain')->withArgs([[$sendExportEmail]])->once()->andReturnSelf();

        $export = Mockery::mock(OrderInvoicesSuppliersExport::class);
        $export->shouldReceive('queue')
            ->withArgs(['customers-2022-05.csv', 'exports', 'Csv'])
            ->once()
            ->andReturn($queue);
        App::bind(OrderInvoicesSuppliersExport::class, fn() => $export);

        $this->artisan('export:invoices-customers')->assertSuccessful();
    }
}
