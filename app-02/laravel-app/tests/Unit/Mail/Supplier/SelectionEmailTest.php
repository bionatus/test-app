<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\SelectionEmail;
use App\Models\Supplier;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class SelectionEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SelectionEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $supplierName = 'John Doe';

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName]);

        $baseLiveUrl      = Config::get('live.url');
        $accountCustomers = Config::get('live.account.customers');
        $accountUrl       = $baseLiveUrl . $accountCustomers;
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $mailable = new SelectionEmail($supplier);

        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($accountUrl);
        $mailable->assertSeeInHtml($notificationsUrl);
        $mailable->assertSeeInHtml($youtubeLink);
        $mailable->assertSeeInHtml($linkedinLink);
        $mailable->assertSeeInHtml($contactUsLink);
        $mailable->assertSeeInHtml($mainLogoUrl);
        $mailable->assertSeeInHtml($smallLogoUrl);
        $mailable->assertSeeInHtml($youtubeLogoUrl);
        $mailable->assertSeeInHtml($linkedinLogoUrl);
        $mailable->assertSeeInHtml($mailLogoUrl);
    }
}
