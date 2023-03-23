<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\OrderCreationEmail;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class OrderCreationEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderCreationEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $supplierName  = 'John Doe';
        $address       = '123 Street';
        $userFirstName = 'Jane';
        $userLastName  = 'Doe';
        $userName      = $userFirstName . ' ' . $userLastName;

        $supplier = Supplier::factory()->createQuietly(['name' => $supplierName, 'address' => $address]);
        $user     = User::factory()->create(['first_name' => $userFirstName, 'last_name' => $userLastName]);
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();

        Config::set('live.url', $baseLiveUrl = 'https://live-url.com/');
        Config::set('live.routes.inbound', $sectionUrl = '#/inbound');

        $inboundUrl       = $baseLiveUrl . $sectionUrl;
        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $mailable = new OrderCreationEmail($order);

        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($address);
        $mailable->assertSeeInHtml($userName);
        $mailable->assertSeeInHtml($userFirstName);
        $mailable->assertSeeInHtml($inboundUrl);
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
