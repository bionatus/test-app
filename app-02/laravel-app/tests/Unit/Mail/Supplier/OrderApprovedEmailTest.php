<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\OrderApprovedEmail;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class OrderApprovedEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderApprovedEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $bidNumber     = 12345;
        $supplierName  = 'John Doe';
        $companyName   = 'Test Store';
        $userFirstName = 'Jane';
        $userLastName  = 'Doe';
        $userName      = $userFirstName . ' ' . $userLastName;
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
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create(['bid_number' => $bidNumber]);
        $company  = Company::factory()->create(['name' => $companyName]);
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();

        $outboundUrl = Config::get('live.url') . Config::get('live.routes.outbound');

        $mailable = new OrderApprovedEmail($order);

        $mailable->assertSeeInHtml($bidNumber);
        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($companyName);
        $mailable->assertSeeInHtml($userName);
        $mailable->assertSeeInHtml($outboundUrl);
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
