<?php

namespace Tests\Unit\Mail\OrderInvoice;

use App\Mail\OrderInvoice\ExportEmail;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExportEmailTest extends TestCase
{
    /** @test */
    public function it_shows_correct_fields()
    {
        $filePath = '';
        $type = 'test type';
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $mailable = new ExportEmail($filePath, $type);

        $mailable->assertSeeInHtml($type);
        $mailable->assertSeeInHtml($fromDate);
        $mailable->assertSeeInHtml($tillDate);
    }

    /** @test */
    public function it_has_an_attachment()
    {
        $filePath = '/foo/bar.doc';
        $type = '';

        $mailable = new ExportEmail($filePath, $type);
        $mailable->build();

        $attachments = $mailable->attachments;

        $this->assertCount(1, $attachments);
        $this->assertSame($filePath, $attachments[0]['file']);
        $this->assertSame('text/csv', $attachments[0]['options']['mime']);
    }
}
