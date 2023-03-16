<?php

namespace Tests\Unit\Jobs\OrderInvoice;

use App\Jobs\OrderInvoice\SendExportEmail;
use App\Mail\OrderInvoice\ExportEmail;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mail;
use ReflectionClass;
use Tests\TestCase;

class SendExportEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendExportEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_sends_an_export_email_to_configured_emails()
    {
        $emails = ['account@test.com', 'otheraccount@test.com'];
        Config::set('mail.reports.invoices', $emails);

        Mail::fake();

        $subject = 'Export Invoices';
        $job     = new SendExportEmail('Invoices', 'fake/filepath', $subject);
        $job->handle();
        Mail::assertSent(ExportEmail::class, function(ExportEmail $mailable) use ($subject, $emails) {
            $receivers = Collection::make($mailable->to)->plucK('address');
            $this->assertEqualsCanonicalizing($receivers->toArray(), $emails);
            $mailable->build();

            return $mailable->subject === $subject;
        });
    }
}
