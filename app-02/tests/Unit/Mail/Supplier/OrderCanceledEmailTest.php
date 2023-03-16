<?php

namespace Tests\Unit\Mail\Supplier;

use App\Mail\Supplier\OrderCanceledEmail;
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

class OrderCanceledEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderCanceledEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_shows_correct_fields()
    {
        $supplier     = Supplier::factory()->createQuietly([
            'address' => $supplierAddress = '123 Street',
            'name'    => $supplierName = 'Supplier Name',
        ]);
        $user         = User::factory()->create([
            'first_name' => $userFirstName = 'User',
            'last_name'  => $userLastName = 'Name',
        ]);
        $userFullName = trim($userFirstName . ' ' . $userLastName);
        $company      = Company::factory()->create(['name' => $companyName = 'John Doe']);

        CompanyUser::factory()->usingCompany($company)->usingUser($user)->create();
        $order = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->create(['bid_number' => $bidNumber = 12345]);

        Config::set('live.url', $baseLiveUrl = 'https://test.com/');
        Config::set('live.routes.outbound', $outboundUrl = '#/test-outbound');

        $linkUrl = $baseLiveUrl . $outboundUrl;

        $notificationsUrl = $baseLiveUrl . Config::get('live.account.notifications');
        $youtubeLink      = 'https://www.youtube.com/channel/UCm461wt_4Q0zADVuyojmOMA';
        $linkedinLink     = 'https://www.linkedin.com/company/bluon-inc/';
        $contactUsLink    = 'mailto:contactus@bluon.com';
        $mainLogoUrl      = 'images/bluon-logo-live.png';
        $smallLogoUrl     = 'images/bluon-logo-small.png';
        $youtubeLogoUrl   = 'images/youtube-logo-small.png';
        $linkedinLogoUrl  = 'images/in-logo-small.png';
        $mailLogoUrl      = 'images/mail-logo-small.png';

        $mailable = new OrderCanceledEmail($order);

        $mailable->assertSeeInHtml($bidNumber);
        $mailable->assertSeeInHtml($companyName);
        $mailable->assertSeeInHtml($linkUrl);
        $mailable->assertSeeInHtml($supplierAddress);
        $mailable->assertSeeInHtml($supplierName);
        $mailable->assertSeeInHtml($userFullName);
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
