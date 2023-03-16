<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\NewMessageEmail;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class NewMessageEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(NewMessageEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_shows_correct_fields_with_user_with_no_orders_with_the_supplier_with_timezone()
    {
        $supplierName     = 'John Doe';
        $userFirstName    = 'Jane';
        $userLastName     = 'Doe';
        $timeZone         = 'Europe/London';
        $userName         = $userFirstName . ' ' . $userLastName;
        $message          = 'This is a test message';
        $datetime         = Carbon::now()->tz($timeZone)->format('M jS, h:i A');
        $notificationsUrl = Config::get('live.url') . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName, 'timezone' => $timeZone]);
        $user     = User::factory()->create(['first_name' => $userFirstName, 'last_name' => $userLastName]);

        $mailable = new NewMessageEmail($supplier, $user, $message, $linkUrl = 'https://link.com');

        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($userName);
        $mailable->assertSeeInHtml($message);
        $mailable->assertSeeInHtml($datetime);
        $mailable->assertSeeInHtml($linkUrl);
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

    /** @test */
    public function it_shows_correct_fields_with_user_with_at_least_one_pending_or_pending_approval_order_with_the_user_with_null_supplier_timezone(
    )
    {
        $supplierName     = 'John Doe';
        $userFirstName    = 'Jane';
        $userLastName     = 'Doe';
        $timeZone         = null;
        $userName         = $userFirstName . ' ' . $userLastName;
        $message          = 'This is a test message';
        $datetime         = Carbon::now()->tz($timeZone)->format('M jS, h:i A');
        $notificationsUrl = Config::get('live.url') . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName, 'timezone' => $timeZone]);
        $user     = User::factory()->create(['first_name' => $userFirstName, 'last_name' => $userLastName]);
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();

        $mailable = new NewMessageEmail($supplier, $user, $message, $linkUrl = 'https://link.com');

        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($userName);
        $mailable->assertSeeInHtml($message);
        $mailable->assertSeeInHtml($datetime);
        $mailable->assertSeeInHtml($linkUrl);
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

    /** @test */
    public function it_shows_correct_fields_with_user_with_no_pending_or_pending_approval_orders_with_the_user_with_default_supplier_timezone(
    )
    {
        $supplierName     = 'John Doe';
        $userFirstName    = 'Jane';
        $userLastName     = 'Doe';
        $userName         = $userFirstName . ' ' . $userLastName;
        $message          = 'This is a test message';
        $datetime         = Carbon::now()->format('M jS, h:i A');
        $notificationsUrl = Config::get('live.url') . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName]);
        $user     = User::factory()->create(['first_name' => $userFirstName, 'last_name' => $userLastName]);
        Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();

        $mailable = new NewMessageEmail($supplier, $user, $message, $linkUrl = 'https://link.com');

        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($userName);
        $mailable->assertSeeInHtml($message);
        $mailable->assertSeeInHtml($datetime);
        $mailable->assertSeeInHtml($linkUrl);
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
